<?php

declare(strict_types=1);

namespace Tests\Php\Php\User\Presentation;

use DateTimeImmutable;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\User\Application\SendLoginCodeInterface;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Presentation\LoginController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginControllerMagicLinkTest extends TestCase
{
    /** @var RequestHandlerInterface|MockObject */
    private RequestHandlerInterface|MockObject $requestHandler;

    /** @var MockObject|SendLoginCodeInterface */
    private SendLoginCodeInterface|MockObject $loginCode;

    /** @var SessionInterface|MockObject */
    private SessionInterface|MockObject $session;

    /** @var UserRepositoryInterface|MockObject */
    private UserRepositoryInterface|MockObject $userRepository;

    private const int LOGIN_CODE_EXPIRY_TIME = 15;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->loginCode = $this->createMock(SendLoginCodeInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->requestHandler->expects($this->once())
            ->method('validate');
    }

    public function testLoginWithMagicLinkUserNotFound(): void
    {
        $expectedResponse = [
            'message' => 'User not found'
        ];
        $vars = new DataSet([
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ]);
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","magicLink":"e762349c-a60e-4428-b781-a076e161f1e3"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn(null);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_NOT_FOUND,
                '',
                'login.invalid_link',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_NOT_FOUND));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals($expectedResponse, json_decode((string) $response->getContent(), true));
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testLoginWithMagicLinkOutOfTime(): void
    {
        $expectedResponse = ['error' => 'Could not log you in.'];
        $vars = new DataSet([
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ]);
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","magicLink":"e762349c-a60e-4428-b781-a076e161f1e3"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME + 1) . ' minutes'),
            magicLink: 'e762349c-a60e-4428-b781-a076e161f1e3',
            phoneCode: 'ABC123',
            passcodeRoute: PasscodeRoute::Email,
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_REQUEST_TIMEOUT,
                '',
                'login.error',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_REQUEST_TIMEOUT));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals($expectedResponse, json_decode((string) $response->getContent(), true));
        $this->assertSame(408, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testLoginWithMagicLink(): void
    {
        $vars = new DataSet([
            'email' => 'test@example.tld',
            'magicLink' => 'e762349c-a60e-4428-b781-a076e161f1e3'
        ]);
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","magicLink":"e762349c-a60e-4428-b781-a076e161f1e3"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'test@example.tld',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME - 10) . ' minutes'),
            magicLink: 'e762349c-a60e-4428-b781-a076e161f1e3',
            phoneCode: null,
            passcodeRoute: PasscodeRoute::Email,
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserFromMagicLink')
            ->with('e762349c-a60e-4428-b781-a076e161f1e3')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new Response('<html lang="en"><body><p>You are logged in</p></body></html>'));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->loginWithMagicLink($request, $vars);

        $this->assertEquals('<html lang="en"><body><p>You are logged in</p></body></html>', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($user->isLoggedIn());
    }
}
