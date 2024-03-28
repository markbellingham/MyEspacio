<?php

declare(strict_types=1);

namespace MyEspacio\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginController
{
    private ?User $user;

    private const LOGIN_CODE_EXPIRY_TIME = 5;

    public function __construct(
        private readonly RequestHandler $requestHandler,
        private readonly SendLoginCode $loginCode,
        private readonly SessionInterface $session,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LanguageReader $languageLoader
    ) {
    }

    public function processLoginForm(Request $request): Response
    {
        $this->requestHandler->validate($request);
        $translationIdentifier = new TranslationIdentifier(
            language: $request->attributes->get('language'),
            filename: 'messages'
        );

        if ($this->session->get('user')) {
            return $this->requestHandler->sendResponse([
                'success' => false,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, key: 'login.already_logged_in')
            ]);
        }

        $vars = json_decode($request->getContent(), true);
        $this->user = $this->getUserByLoginValues($vars);
        if (!$this->user) {
            return $this->requestHandler->sendResponse([
                'success' => false,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, key: 'login.user_not_found')
            ]);
        }

        if (($vars['phone_code'] ?? '') !== '') {
            return $this->logUserIn($translationIdentifier, $vars);
        }

        try {
            $this->loginCode->generateCode($this->user);
        } catch (Exception) {
            return $this->requestHandler->sendResponse([
                'success' => false,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, key: 'login.generic_error')
            ]);
        }

        if (
            $this->userRepository->saveLoginDetails($this->user) === false ||
            $this->loginCode->sendToUser($this->user) === false
        ) {
            return $this->requestHandler->sendResponse([
                'success' => false,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, key: 'login.generic_error')
            ]);
        }

        return $this->requestHandler->sendResponse([
            'success' => true,
            'message' => $this->languageLoader->getTranslationText(
                identifier: $translationIdentifier,
                key: 'login.code_sent',
                variables: ['passcode_route' => $this->user->getPasscodeRoute()]
            )
        ]);
    }

    private function getUserByLoginValues(array $vars): ?User
    {
        if (($vars['email'] ?? '') !== '') {
            $this->user = $this->userRepository->getUserByLoginValues('email', $vars['email']);
        } elseif (($vars['phone'] ?? '') !== '') {
            $this->user = $this->userRepository->getUserByLoginValues('phone', $vars['phone']);
        }

        if ($this->user && ($vars['phone_code'] ?? '') !== '') {
            $this->user->setPasscodeRoute('phone');
        }

        return $this->user ?: null;
    }

    public function loginWithMagicLink(Request $request, array $vars): Response
    {
        $this->requestHandler->validate($request);
        $translationIdentifier = new TranslationIdentifier(
            language: $request->attributes->get('language'),
            filename: 'login'
        );

        $this->user = $this->userRepository->getUserFromMagicLink($vars['magicLink']);
        if (!$this->user) {
            return $this->requestHandler->sendResponse([
                'success' => false,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, 'login.invalid_link')
            ]);
        }

        if ($this->secondLoginRequestInTime()) {
            $this->setUserLoggedIn();
            return $this->requestHandler->showRoot($request, $vars);
        }

        return $this->requestHandler->sendResponse([
            'success' => false,
            'message' => $this->languageLoader->getTranslationText($translationIdentifier, 'login.error')
        ]);
    }

    private function logUserIn(TranslationIdentifier $translationIdentifier, array $vars): Response
    {
        if ($this->loginValuesCheckout($vars)) {
            $this->setUserLoggedIn();
            return $this->requestHandler->sendResponse([
                'success' => true,
                'message' => $this->languageLoader->getTranslationText($translationIdentifier, 'login.logged_in'),
                'username' => $this->user->getName()
            ]);
        }

        return $this->requestHandler->sendResponse([
            'success' => false,
            'message' => $this->languageLoader->getTranslationText($translationIdentifier, 'login.error')
        ]);
    }

    private function loginValuesCheckout(array $vars): bool
    {
        if (!$this->secondLoginRequestInTime()) {
            return false;
        }

        if ($this->user->getPasscodeRoute() == 'phone') {
            return trim($vars['phone_code'] ?? '') === $this->user->getPhoneCode();
        }

        if ($this->user->getPasscodeRoute() == 'email') {
            return trim($vars['magicLink'] ?? '') === $this->user->getMagicLink();
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

    public function logout(Request $request, array $vars): Response
    {
        $this->requestHandler->validate($request);
        $translationIdentifier = new TranslationIdentifier(
            language: $request->attributes->get('language'),
            filename: 'login'
        );

        $this->session->remove('user');
        return $this->requestHandler->sendResponse([
            'success' => true,
            'message' => $this->languageLoader->getTranslationText($translationIdentifier, 'login.logged_out')
        ]);
    }
}
