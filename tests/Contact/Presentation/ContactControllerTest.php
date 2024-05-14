<?php

declare(strict_types=1);

namespace Tests\Contact\Presentation;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Contact\Presentation\ContactController;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Messages\EmailInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ContactControllerTest extends TestCase
{
    public function testShow()
    {
        $requestHandler = $this->createMock(RequestHandler::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(Captcha::class);

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

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('sendResponse')
            ->willReturn(new Response($expectedResponse));
        $captcha->expects($this->once())
            ->method('getIcons')
            ->with(ContactController::CAPTCHA_ICONS_QUANTITY)
            ->willReturn($captchaIcons);
        $session->expects($this->once())
            ->method('set')
            ->with('contactIcons', $captchaIcons->toArray());

        $request = new Request();
        $controller = new ContactController($requestHandler, $session, $email, $captcha);

        $response = $controller->show($request, []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }
}
