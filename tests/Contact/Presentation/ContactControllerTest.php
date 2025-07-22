<?php

declare(strict_types=1);

namespace Tests\Contact\Presentation;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Contact\Presentation\ContactController;
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
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);

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

        $selectedIcon = new CaptchaIcon();
        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
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
        $session->expects($this->once())
            ->method('set')
            ->with('contactIcons', $captchaIcons->toArray());
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->with(new ResponseData(
                [
                        'icons' => $captchaIcons,
                        'captcha1' => $selectedIcon,
                        'captcha2' => ''
                    ],
                Response::HTTP_OK,
                'contact/Contact.html.twig'
            ))
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController($requestHandler, $session, $email, $captcha);

        $response = $controller->show($request, []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testShowWithRoot(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);

        $expectedResponse = 'Rendered HTML Root Content';

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController($requestHandler, $session, $email, $captcha);
        $response = $controller->show($request, []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testSendMessageNoToken(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(CaptchaInterface::class);
        $request = new Request();

        $expectedResponse = 'Rendered HTML Root Content';

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new Response($expectedResponse));

        $controller = new ContactController($requestHandler, $session, $email, $captcha);
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
        $request = new Request();

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(
                ['error' => 'Invalid Message'],
                Response::HTTP_BAD_REQUEST
            ));

        $controller = new ContactController($requestHandler, $session, $email, $captcha);
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
        $request = new Request();

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(['error' => 'Invalid Message - emailAddress: , message: , name: , subject: , captchaIconId: , description:'], Response::HTTP_BAD_REQUEST));

        $controller = new ContactController($requestHandler, $session, $email, $captcha);
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
        $request = new Request(
            query: [],
            request: [
                'emailAddress' => 'mail@domain.tld',
                'name' => 'Mark',
                'subject' => 'subject',
                'message' => 'message to the website admin',
                'captcha1' => '1',
                'description' => '',
            ],
        );

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $email->expects($this->once())
            ->method('send')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new JsonResponse(['error' => 'Invalid Message'], Response::HTTP_BAD_REQUEST));

        $controller = new ContactController($requestHandler, $session, $email, $captcha);
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
        $request = new Request(
            query: [],
            request: [
                'emailAddress' => 'mail@domain.tld',
                'name' => 'Mark',
                'subject' => 'subject',
                'message' => 'message to the website admin',
                'captcha1' => '1',
                'description' => '',
            ],
        );

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $email->expects($this->once())
            ->method('send')
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

        $controller = new ContactController($requestHandler, $session, $email, $captcha);
        $response = $controller->sendMessage($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"captcha":{},"message":"Success! Message Sent."}', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }
}
