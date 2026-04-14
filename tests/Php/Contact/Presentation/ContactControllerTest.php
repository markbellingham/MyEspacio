<?php

declare(strict_types=1);

namespace Tests\Php\Contact\Presentation;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Contact\Application\ContactMeBuilderInterface;
use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Contact\Presentation\ContactController;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Messages\EmailInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ContactControllerTest extends TestCase
{
    public function testShow(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $expectedResponse = '{}';

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->never())
            ->method('getIcons');
        $captcha->expects($this->never())
            ->method('getSelectedIcon');
        $captcha->expects($this->never())
            ->method('getEncryptedIcon');
        $session->expects($this->never())
            ->method('set');
        $session->expects($this->never())
            ->method('get');
        $contactMeBuilder->expects($this->never())
            ->method('build');
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                statusCode: Response::HTTP_OK,
                template: 'contact/Contact.html.twig'
            ))
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController(
            $requestHandler,
            $session,
            $this->createMock(EmailInterface::class),
            $captcha,
            $contactMeBuilder,
        );

        $response = $controller->show($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testShowWithRoot(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $captchaIcons = new CaptchaIconCollection([
            [
                'icon_id' => '1',
                'icon' => '<i class="bi bi-phone-vibrate"></i>',
                'name' => 'Mobile'
            ],
            [
                'icon_id' => '2',
                'icon' => '<i class="bi bi-keyboard"></i>',
                'name' => 'Keyboard'
            ]
        ]);
        $expectedResponse = 'Rendered HTML Content';
        $selectedIcon = new CaptchaIcon(
            iconId: 1,
            icon: '<i class="bi bi-phone-vibrate"></i>',
            name: 'Mobile',
            colour: 'btn-warning'
        );

        $captcha->expects($this->once())
            ->method('getIcons')
            ->with(ContactController::CAPTCHA_ICONS_QUANTITY)
            ->willReturn($captchaIcons);
        $captcha->expects($this->once())
            ->method('getSelectedIcon')
            ->willReturn($selectedIcon);
        $captcha->expects($this->once())
            ->method('getEncryptedIcon')
            ->willReturn('');
        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $session->expects($this->once())
            ->method('get')
            ->with('user')
            ->willreturn(null);
        $contactMeBuilder->expects($this->never())
            ->method('build');
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                data: [
                    'icons' => $captchaIcons,
                    'captcha1' => $selectedIcon,
                    'captcha2' => '',
                    'user' => null,
                ],
                template: 'contact/Contact.html.twig',
            ))
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->show($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testSendMessageNoToken(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $request = new Request();

        $expectedResponse = 'Rendered HTML Root Content';

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new Response($expectedResponse));
        $contactMeBuilder->expects($this->never())
            ->method('build');
        $email->expects($this->never())
            ->method('send');
        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testSendMessageBadCaptcha(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);
        $request = new Request();

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $contactMeBuilder->expects($this->never())
            ->method('build');
        $email->expects($this->never())
            ->method('send');
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(
                ['error' => 'Invalid Message'],
                Response::HTTP_BAD_REQUEST
            ));

        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":"Invalid Message"}', $response->getContent());
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testSendMessageBadMessage(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);
        $request = new Request();

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $contactMeBuilder->expects($this->once())
            ->method('build')
            ->with(new DataSet([]))
            ->willThrowException(new InvalidEmailException('Invalid Message - emailAddress: , message: , name: , subject: , captchaIconId: , description:'));
        $email->expects($this->never())
            ->method('send');
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(['error' => 'Invalid Message - emailAddress: , message: , name: , subject: , captchaIconId: , description:'], Response::HTTP_BAD_REQUEST));

        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":"Invalid Message - emailAddress: , message: , name: , subject: , captchaIconId: , description:"}', $response->getContent());
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testSendMessageSendFail(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $requestParams = [
            'emailAddress' => 'mail@domain.tld',
            'name' => 'Mark',
            'subject' => 'subject',
            'message' => 'message to the website admin',
            'captcha1' => '1',
            'description' => '',
        ];
        $request = new Request(
            query: [],
            request: $requestParams,
        );
        $contactMeMessage = new ContactMeMessage(
            emailAddress: 'mail@domain.tld',
            name: 'Mark',
            subject: 'subject',
            message: 'message to the website admin',
            captchaIconId: 1,
            description: ''
        );

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $contactMeBuilder->expects($this->once())
            ->method('build')
            ->with(new DataSet($requestParams))
            ->willReturn($contactMeMessage);
        $email->expects($this->once())
            ->method('send')
            ->with($contactMeMessage)
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(['error' => 'Invalid Message'], Response::HTTP_BAD_REQUEST));

        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":"Invalid Message"}', $response->getContent());
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testSendMessageSendSuccess(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $requestParams = [
            'emailAddress' => 'mail@domain.tld',
            'name' => 'Mark',
            'subject' => 'subject',
            'message' => 'message to the website admin',
            'captcha1' => '1',
            'description' => '',
        ];
        $request = new Request(
            query: [],
            request: $requestParams,
        );
        $contactMeMessage = new ContactMeMessage(
            emailAddress: 'mail@domain.tld',
            name: 'Mark',
            subject: 'subject',
            message: 'message to the website admin',
            captchaIconId: 1,
            description: ''
        );

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $contactMeBuilder->expects($this->once())
            ->method('build')
            ->with(new DataSet($requestParams))
            ->willReturn($contactMeMessage);
        $email->expects($this->once())
            ->method('send')
            ->with($contactMeMessage)
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(
                [
                    'captcha' => $captcha,
                    'message' => 'Success! Message Sent.'
                ],
                Response::HTTP_OK
            ));

        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"captcha":{},"message":"Success! Message Sent."}', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSendCleanMessage(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $contactMeBuilder = $this->createMock(ContactMeBuilderInterface::class);

        $requestParams = [
            'emailAddress' => 'mail@domain.tld',
            'name' => 'Mark',
            'subject' => 'subject',
            'message' => 'message to the website admin<script>alert(1)</script>',
            'captcha1' => '1',
            'description' => '',
        ];
        $request = new Request(
            query: [],
            request: $requestParams,
        );
        $contactMeMessage = new ContactMeMessage(
            emailAddress: 'mail@domain.tld',
            name: 'Mark',
            subject: 'subject',
            message: 'message to the website admin',
            captchaIconId: 1,
            description: '',
        );

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $contactMeBuilder->expects($this->once())
            ->method('build')
            ->with(new DataSet($requestParams))
            ->willReturn($contactMeMessage);
        $email->expects($this->once())
            ->method('send')
            ->with($contactMeMessage)
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(
                [
                    'captcha' => $captcha,
                    'message' => 'Success! Message Sent.'
                ],
                Response::HTTP_OK
            ));

        $controller = new ContactController(
            $requestHandler,
            $session,
            $email,
            $captcha,
            $contactMeBuilder,
        );
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"captcha":{},"message":"Success! Message Sent."}', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }
}
