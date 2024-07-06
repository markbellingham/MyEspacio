<?php

declare(strict_types=1);

namespace Tests\User\Presentation;

use DateTimeImmutable;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Presentation\LoginController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginControllerMagicLinkTest extends TestCase
{
    private RequestHandler $requestHandler;
    private SendLoginCode $loginCode;
    private SessionInterface $session;
    private UserRepositoryInterface $userRepository;
    private LanguageReader $languageReader;

    private const LOGIN_CODE_EXPIRY_TIME = 15;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestHandler = $this->createMock(RequestHandler::class);
        $this->loginCode = $this->createMock(SendLoginCode::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->languageReader = $this->createMock(LanguageReader::class);

        $this->requestHandler->expects($this->once())
            ->method('validate');
    }

    public function testLoginWithMagicLinkUserNotFound(): void
    {
        $vars = [
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ];
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($vars)
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn(null);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('User not found');

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function ($responseData) {
                return new JsonResponse($responseData, Response::HTTP_NOT_FOUND);
            });

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals(['error' => 'User not found'], json_decode($response->getContent(), true));
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testLoginWithMagicLinkOutOfTime(): void
    {
        $vars = [
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ];
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($vars)
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME + 1) . ' minutes'),
            magicLink: 'e762349c-a60e-4428-b781-a076e161f1e3',
            phoneCode: 'ABC123',
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn($user);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Could not log you in.');

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function ($responseData) {
                return new JsonResponse($responseData, Response::HTTP_REQUEST_TIMEOUT);
            });

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals(['error' => 'Could not log you in.'], json_decode($response->getContent(), true));
        $this->assertSame(408, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testLoginWithMagicLink(): void
    {
        $vars = [
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ];
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode($vars)
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'test@example.tld',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME - 10) . ' minutes'),
            magicLink: 'e762349c-a60e-4428-b781-a076e161f1e3',
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('showRoot')
            ->willReturn(new Response('<html lang="en"><body><p>You are logged in</p></body></html>'));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals('<html lang="en"><body><p>You are logged in</p></body></html>', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($user->isLoggedIn());
    }
}
