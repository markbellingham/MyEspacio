<?php

declare(strict_types=1);

namespace Tests\User\Presentation;

use DateTimeImmutable;
use Exception;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Presentation\LoginController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LoginControllerTest extends TestCase
{
    private RequestHandler $requestHandler;
    private SendLoginCode $loginCode;
    private SessionInterface $session;
    private UserRepositoryInterface $userRepository;
    private LanguageReader $languageReader;
    private TranslationIdentifierFactory $translatorIdentifierFactory;

    private const LOGIN_CODE_EXPIRY_TIME = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestHandler = $this->createMock(RequestHandler::class);
        $this->loginCode = $this->createMock(SendLoginCode::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->languageReader = $this->createMock(LanguageReader::class);
        $this->translatorIdentifierFactory = $this->createMock(TranslationIdentifierFactory::class);

        $this->requestHandler->expects($this->once())
            ->method('validate');

        $this->requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function ($responseData) {
                return new JsonResponse($responseData);
            });
    }

    public function testProcessLoginFormUserAlreadyLoggedIn()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'You are already logged in.'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormUserAlreadyLoggedInSpanish()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Ya has iniciado sesión'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormUserNotFound()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'User not found'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormCodeSentByEmail()
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
            ->method('sendToUser')
            ->willReturn(true);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Please check your email for the login code');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => true,
            'message' => 'Please check your email for the login code'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormCodeSentByPhone()
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
            ->method('sendToUser')
            ->willReturn(true);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Please check your phone for the login code');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => true,
            'message' => 'Please check your phone for the login code'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormLoginCodeThrewException()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Something went wrong, please contact the website administrator'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormLoginDetailsNotSaved()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Something went wrong, please contact the website administrator'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormEmailCodeNotSent()
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
            ->method('sendToUser')
            ->willReturn(false);

        $this->languageReader->expects($this->once())
            ->method('getTranslationText')
            ->willReturn('Something went wrong, please contact the website administrator');

        $loginController = new LoginController(
            $this->requestHandler,
            $this->loginCode,
            $this->session,
            $this->userRepository,
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Something went wrong, please contact the website administrator'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormLogUserInOutOfTime()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Could not log you in.'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormLogUserInWrongCode()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => false,
            'message' => 'Could not log you in.'
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    public function testProcessLoginFormLogUserIn()
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
            $this->languageReader,
            $this->translatorIdentifierFactory
        );

        $response = $loginController->processLoginForm($request);

        $expectedResponse = [
            'success' => true,
            'message' => 'You are now logged in',
            'username' => $user->getName()
        ];
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }
}
