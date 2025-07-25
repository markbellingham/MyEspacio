<?php

declare(strict_types=1);

namespace Tests\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\User\Application\SendLoginCodeInterface;
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

final class LoginControllerTest extends TestCase
{
    private const int LOGIN_CODE_EXPIRY_TIME = 15;

    /** @var MockObject|RequestHandlerInterface */
    private MockObject|RequestHandlerInterface $requestHandler;

    /** @var MockObject|SendLoginCodeInterface */
    private SendLoginCodeInterface|MockObject $loginCode;

    /** @var MockObject|SessionInterface  */
    private SessionInterface|MockObject $session;

    /** @var MockObject|UserRepositoryInterface */
    private MockObject|UserRepositoryInterface $userRepository;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
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

    public function testProcessLoginFormUserAlreadyLoggedIn(): void
    {
        $expectedResponse = ['error' => 'You are already logged in.'];

        $request = Request::createFromGlobals();
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(['user_id' => 123]);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_CONFLICT,
                '',
                'login.already_logged_in',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_CONFLICT));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(409, $response->getStatusCode());
    }

    public function testProcessLoginFormUserAlreadyLoggedInSpanish(): void
    {
        $expectedResponse = ['error' => 'Ya has iniciado sesión'];

        $request = Request::createFromGlobals();
        $request->attributes->set('language', 'es');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(['user_id' => 123]);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_CONFLICT,
                '',
                'login.already_logged_in',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_CONFLICT));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(409, $response->getStatusCode());
    }

    public function testProcessLoginFormUserNotFound(): void
    {
        $expectedResponse = ['error' => 'User not found'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $this->userRepository->expects($this->once())
            ->method('getUserByEmailAddress')
            ->with('test@example.tld')
            ->willReturn(null);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_NOT_FOUND,
                '',
                'login.user_not_found',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_NOT_FOUND));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testProcessLoginFormCodeSentByEmail(): void
    {
        $expectedResponse = ['message' => 'Please check your email for the login code'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: null,
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByEmailAddress')
            ->with('test@example.tld')
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('saveLoginDetails')
            ->with($user)
            ->willReturn(true);

        $this->loginCode->expects($this->once())
            ->method('sendTo')
            ->willReturn(true);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_OK,
                '',
                'login.code_sent',
                ['passcode_route' => 'email']
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_OK));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormCodeSentByPhone(): void
    {
        $expectedResponse = ['message' => 'Please check your phone for the login code'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"phone":"01234567890"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: null,
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByPhoneNumber')
            ->with('01234567890')
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('saveLoginDetails')
            ->with($user)
            ->willReturn(true);

        $this->loginCode->expects($this->once())
            ->method('sendTo')
            ->willReturn(true);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_OK,
                '',
                'login.code_sent',
                ['passcode_route' => 'phone']
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_OK));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLoginCodeThrewException(): void
    {
        $expectedResponse = ['error' => 'Something went wrong, please contact the website administrator'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"phone":"01234567890"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: null,
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByPhoneNumber')
            ->with('01234567890')
            ->willReturn($user);

        $this->loginCode->expects($this->once())
            ->method('generateCode')
            ->willThrowException(new Exception('Not enough entropy available to satisfy your key length requirement'));

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '',
                'login.generic_error',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_INTERNAL_SERVER_ERROR));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLoginDetailsNotSaved(): void
    {
        $expectedResponse = ['error' => 'Something went wrong, please contact the website administrator'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"phone":"01234567890"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: null,
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByPhoneNumber')
            ->with('01234567890')
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('saveLoginDetails')
            ->with($user)
            ->willReturn(false);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '',
                'login.generic_error',
                []
            ))
            ->willreturn(new JsonResponse($expectedResponse, Response::HTTP_INTERNAL_SERVER_ERROR));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormEmailCodeNotSent(): void
    {
        $expectedResponse = ['error' => 'Something went wrong, please contact the website administrator'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"phone":"01234567890"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: new DateTimeImmutable(),
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByPhoneNumber')
            ->with('01234567890')
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('saveLoginDetails')
            ->with($user)
            ->willReturn(true);

        $this->loginCode->expects($this->once())
            ->method('sendTo')
            ->willReturn(false);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                '',
                'login.generic_error',
                []
            ))
            ->willreturn(new JsonResponse($expectedResponse, Response::HTTP_INTERNAL_SERVER_ERROR));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserInOutOfTime(): void
    {
        $expectedResponse = ['error' => 'Could not log you in.'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","phone_code":"ABC123"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME + 1) . ' minutes'),
            magicLink: null,
            phoneCode: 'ABC123',
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByEmailAddress')
            ->with('test@example.tld')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_BAD_REQUEST,
                '',
                'login.error',
                []
            ))
            ->willreturn(new JsonResponse($expectedResponse, Response::HTTP_BAD_REQUEST));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserInWrongCode(): void
    {
        $expectedResponse = ['error' => 'Could not log you in.'];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","phone_code":"XYZ321"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME - 1) . ' minutes'),
            magicLink: null,
            phoneCode: 'ABC123',
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByEmailAddress')
            ->with('test@example.tld')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_BAD_REQUEST,
                '',
                'login.error',
                []
            ))
            ->willreturn(new JsonResponse($expectedResponse, Response::HTTP_BAD_REQUEST));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserIn(): void
    {
        $expectedResponse = [
            'message' => 'You are now logged in',
            'username' => 'Mark'
        ];

        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"email":"test@example.tld","phone_code":"ABC123"}'
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $loginDate = new DateTimeImmutable();
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 0,
            loginDate: $loginDate->modify('-' . (self::LOGIN_CODE_EXPIRY_TIME - 1) . ' minutes'),
            magicLink: null,
            phoneCode: 'ABC123',
            passcodeRoute: 'email',
            id: 1
        );

        $this->userRepository->expects($this->once())
            ->method('getUserByEmailAddress')
            ->with('test@example.tld')
            ->willReturn($user);

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                ['username' => 'Mark'],
                Response::HTTP_OK,
                '',
                'login.logged_in',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_OK));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals($expectedResponse, json_decode((string) $response->getContent(), true));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($user->isLoggedIn());
    }

    public function testLogOut(): void
    {
        $expectedResponse = ['message' => 'You are now logged out'];

        $request = Request::create('/logout');
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('remove')
            ->with('user');

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [],
                Response::HTTP_OK,
                '',
                'login.logged_out',
                []
            ))
            ->willReturn(new JsonResponse($expectedResponse, Response::HTTP_OK));

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository
        );

        $response = $loginController->logout($request);

        $this->assertEquals(
            $expectedResponse,
            json_decode((string) $response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
    }
}
