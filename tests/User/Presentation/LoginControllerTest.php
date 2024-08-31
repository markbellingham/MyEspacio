<?php

declare(strict_types=1);

namespace Tests\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\User\Application\SendLoginCodeInterface;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Presentation\LoginController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginControllerTest extends TestCase
{
    private const LOGIN_CODE_EXPIRY_TIME = 15;

    /** @var MockObject|RequestHandlerInterface */
    private MockObject|RequestHandlerInterface $requestHandler;

    /** @var MockObject|SendLoginCodeInterface */
    private SendLoginCodeInterface|MockObject $loginCode;

    /** @var MockObject|SessionInterface  */
    private SessionInterface|MockObject $session;

    /** @var MockObject|UserRepositoryInterface */
    private MockObject|UserRepositoryInterface $userRepository;

    /** @var MockObject|LanguageReader */
    private LanguageReader|MockObject $languageReader;

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
        $this->languageReader = $this->createMock(LanguageReader::class);

        $this->requestHandler->expects($this->once())
            ->method('validate');

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function ($responseData, $template, $statusCode) {
                return new JsonResponse($responseData, $statusCode);
            });
    }

    public function testProcessLoginFormUserAlreadyLoggedIn(): void
    {
        $request = Request::createFromGlobals();
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(['user_id' => 123]);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('You are already logged in.');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'You are already logged in.'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(409, $response->getStatusCode());
    }

    public function testProcessLoginFormUserAlreadyLoggedInSpanish(): void
    {
        $request = Request::createFromGlobals();
        $request->attributes->set('language', 'es');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(['user_id' => 123]);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Ya has iniciado sesión');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Ya has iniciado sesión'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(409, $response->getStatusCode());
    }

    public function testProcessLoginFormUserNotFound(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'test@example.tld'])
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('User not found');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'User not found'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testProcessLoginFormCodeSentByEmail(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['email' => 'test@example.tld'])
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Please check your email for the login code');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['message' => 'Please check your email for the login code'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormCodeSentByPhone(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['phone' => '01234567890'])
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Please check your phone for the login code');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['message' => 'Please check your phone for the login code'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLoginCodeThrewException(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['phone' => '01234567890'])
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Something went wrong, please contact the website administrator');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Something went wrong, please contact the website administrator'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLoginDetailsNotSaved(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['phone' => '01234567890'])
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Something went wrong, please contact the website administrator');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Something went wrong, please contact the website administrator'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormEmailCodeNotSent(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['phone' => '01234567890'])
        );
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willReturn(null);

        $user = new User(
            email: 'mail@example.com',
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Something went wrong, please contact the website administrator');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Something went wrong, please contact the website administrator'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserInOutOfTime(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'test@example.tld',
                'phone_code' => 'ABC123'
            ])
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
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Could not log you in.');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Could not log you in.'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserInWrongCode(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'test@example.tld',
                'phone_code' => 'XYZ321'
            ])
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
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Could not log you in.');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $this->assertEquals(
            ['error' => 'Could not log you in.'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($user->isLoggedIn());
    }

    public function testProcessLoginFormLogUserIn(): void
    {
        $request = Request::create(
            '/login',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'test@example.tld',
                'phone_code' => 'ABC123'
            ])
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
            uuid: 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
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

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('You are now logged in');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'message' => 'You are now logged in',
            'username' => 'Mark'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($user->isLoggedIn());
    }

    public function testLogOut(): void
    {
        $request = Request::create('/logout');
        $request->attributes->set('language', 'en');
        $request->headers->set('Accept', 'application/json');

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('You are now logged out');

        $this->session->expects($this->once())
            ->method('remove')
            ->with('user');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader
        );

        $response = $loginController->logout($request);

        $this->assertEquals(
            ['message' => 'You are now logged out'],
            json_decode($response->getContent(), true)
        );
        $this->assertSame(200, $response->getStatusCode());
    }
}
