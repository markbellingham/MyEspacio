<?php

declare(strict_types=1);

namespace MyEspacio\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\User\Application\SendLoginCodeInterface;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginController
{
    private ?User $user = null;

    private const int LOGIN_CODE_EXPIRY_TIME = 15;

    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
        private readonly SendLoginCodeInterface $loginCode,
        private readonly SessionInterface $session,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function processLoginForm(Request $request): Response
    {
        $this->requestHandler->validate($request);

        if ($this->session->get('user')) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_CONFLICT,
                    translationKey: 'login.already_logged_in'
                )
            );
        }

        $vars = json_decode($request->getContent(), true);
        /** @var array<string, mixed> $vars */
        $this->user = $this->getUserByLoginValues($vars);
        if ($this->user === null) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'login.user_not_found'
                )
            );
        }

        if (($vars['phone_code'] ?? '') !== '') {
            return $this->logUserIn($vars);
        }

        try {
            $this->loginCode->generateCode($this->user);
        } catch (Exception) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                    translationKey: 'login.generic_error'
                )
            );
        }

        if (
            $this->userRepository->saveLoginDetails($this->user) === false ||
            $this->loginCode->sendTo($this->user) === false
        ) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                    translationKey: 'login.generic_error'
                )
            );
        }

        return $this->requestHandler->sendResponse(
            new ResponseData(
                translationKey: 'login.code_sent',
                translationVariables: ['passcode_route' => $this->user->getPasscodeRoute()->value]
            )
        );
    }

    /**
     * @param array<string, mixed> $vars
     * @return User|null
     */
    private function getUserByLoginValues(array $vars): ?User
    {
        if (
            ($vars['email'] ?? '') !== '' &&
            is_string($vars['email'])
        ) {
            $this->user = $this->userRepository->getUserByEmailAddress($vars['email']);
        } elseif (
            ($vars['phone'] ?? '') !== '' &&
            is_string($vars['phone'])
        ) {
            $this->user = $this->userRepository->getUserByPhoneNumber($vars['phone']);
            $this->user?->setPasscodeRoute(PasscodeRoute::Phone);
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

        if (is_string($vars['magicLink'])) {
            $this->user = $this->userRepository->getUserFromMagicLink($vars['magicLink']);
        }
        if ($this->user === null) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'login.invalid_link'
                )
            );
        }

        if ($this->secondLoginRequestInTime()) {
            $this->setUserLoggedIn();
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    template: '',
                )
            );
        }

        return $this->requestHandler->sendResponse(
            new ResponseData(
                statusCode: Response::HTTP_REQUEST_TIMEOUT,
                translationKey: 'login.error'
            )
        );
    }

    /**
     * @param array<string, mixed> $vars
     * @return Response
     */
    private function logUserIn(array $vars): Response
    {
        if ($this->loginValuesCheckout($vars)) {
            $this->setUserLoggedIn();
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    data: [
                        'username' => $this->user?->getName()
                    ],
                    translationKey: 'login.logged_in'
                )
            );
        }

        return $this->requestHandler->sendResponse(
            new ResponseData(
                statusCode: Response::HTTP_BAD_REQUEST,
                translationKey: 'login.error'
            )
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

        if (isset($vars['phone_code']) && is_string($vars['phone_code'])) {
            return trim($vars['phone_code']) === $this->user?->getPhoneCode();
        }

        return false;
    }

    private function secondLoginRequestInTime(): bool
    {
        $firstLoginTime = $this->user?->getLoginDate();
        if ($firstLoginTime === null) {
            return false;
        }
        try {
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
        $this->user?->setIsLoggedIn(true);
        $this->session->set('user', $this->user);
    }

    public function logout(Request $request): Response
    {
        $this->requestHandler->validate($request);

        $this->session->remove('user');
        return $this->requestHandler->sendResponse(
            new ResponseData(
                translationKey: 'login.logged_out'
            )
        );
    }
}
