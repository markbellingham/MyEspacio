<?php

declare(strict_types=1);

namespace MyEspacio\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginController
{
    private ?User $user;

    private const LOGIN_CODE_EXPIRY_TIME = 15;

    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
        private readonly SendLoginCode $loginCode,
        private readonly SessionInterface $session,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LanguageReader $languageReader,
    ) {
    }

    public function processLoginForm(Request $request): Response
    {
        $this->requestHandler->validate($request);

        if ($this->session->get('user')) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        key: 'login.already_logged_in'
                    )
                ],
                statusCode: Response::HTTP_CONFLICT
            );
        }

        $vars = json_decode($request->getContent(), true);
        $this->user = $this->getUserByLoginValues($vars);
        if ($this->user === null) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        key: 'login.user_not_found'
                    )
                ],
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        if (($vars['phone_code'] ?? '') !== '') {
            return $this->logUserIn($request, $vars);
        }

        try {
            $this->loginCode->generateCode($this->user);
        } catch (Exception) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        key: 'login.generic_error'
                    )
                ],
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if (
            $this->userRepository->saveLoginDetails($this->user) === false ||
            $this->loginCode->sendToUser($this->user) === false
        ) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        key: 'login.generic_error'
                    )
                ],
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->requestHandler->sendResponse(
            data: [
                'message' => $this->languageReader->getTranslationText(
                    identifier: $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    key: 'login.code_sent',
                    variables: ['passcode_route' => $this->user->getPasscodeRoute()]
                )
            ]
        );
    }

    /**
     * @param array<string, mixed> $vars
     * @return User|null
     */
    private function getUserByLoginValues(array $vars): ?User
    {
        if (($vars['email'] ?? '') !== '') {
            $this->user = $this->userRepository->getUserByEmailAddress($vars['email']);
        } elseif (($vars['phone'] ?? '') !== '') {
            $this->user = $this->userRepository->getUserByPhoneNumber($vars['phone']);
            $this->user?->setPasscodeRoute('phone');
        }

        return $this->user ?: null;
    }

    /**
     * @param Request $request
     * @param array<string, mixed> $vars
     * @return Response
     */
    public function loginWithMagicLink(Request $request, array $vars): Response
    {
        $this->requestHandler->validate($request);

        $this->user = $this->userRepository->getUserFromMagicLink($vars['magicLink']);
        if ($this->user === null) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        'login.invalid_link',
                    ),
                ],
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        if ($this->secondLoginRequestInTime()) {
            $this->setUserLoggedIn();
            return $this->requestHandler->showRoot($request, $vars);
        }

        return $this->requestHandler->sendResponse(
            data: [
                'error' => $this->languageReader->getTranslationText(
                    $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    'login.error'
                ),
            ],
            statusCode: Response::HTTP_REQUEST_TIMEOUT
        );
    }

    /**
     * @param Request $request
     * @param array<string, mixed> $vars
     * @return Response
     */
    private function logUserIn(Request $request, array $vars): Response
    {
        if ($this->loginValuesCheckout($vars)) {
            $this->setUserLoggedIn();
            return $this->requestHandler->sendResponse(
                data: [
                    'message' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        'login.logged_in'
                    ),
                    'username' => $this->user->getName()
                ]
            );
        }

        return $this->requestHandler->sendResponse(
            data: [
                'error' => $this->languageReader->getTranslationText(
                    $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    'login.error'
                )
            ],
            statusCode: Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param array<string, mixed> $vars
     * @return bool
     */
    private function loginValuesCheckout(array $vars): bool
    {
        if ($this->secondLoginRequestInTime() === false) {
            return false;
        }

        if (isset($vars['phone_code'])) {
            return trim($vars['phone_code']) === $this->user->getPhoneCode();
        }

        return false;
    }

    private function secondLoginRequestInTime(): bool
    {
        try {
            $firstLoginTime = new DateTimeImmutable($this->user->getLoginDateString());
            $secondLoginTime = new DateTimeImmutable();
            $diff = $firstLoginTime->diff($secondLoginTime);
            $minutes = $diff->days * 24 * 60;
            $minutes += $diff->h * 60;
            $minutes += $diff->i;
            return $minutes < self::LOGIN_CODE_EXPIRY_TIME;
        } catch (Exception) {
            return false;
        }
    }

    private function setUserLoggedIn(): void
    {
        $this->user->setIsLoggedIn(true);
        $this->session->set('user', $this->user);
    }

    public function logout(Request $request): Response
    {
        $this->requestHandler->validate($request);

        $this->session->remove('user');
        return $this->requestHandler->sendResponse(
            [
                'message' => $this->languageReader->getTranslationText(
                    $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    key: 'login.logged_out'
                )
            ]
        );
    }
}
