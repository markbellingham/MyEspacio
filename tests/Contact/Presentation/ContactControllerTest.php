<?php

declare(strict_types=1);

namespace Tests\Contact\Presentation;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
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
            ->with(
                [
                    'icons' => $captchaIcons,
                    'captcha1' => $selectedIcon,
                    'captcha2' => ''
                ],
                'contact/Contact.html.twig'
            )
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController($requestHandler, $session, $email, $captcha);

        $response = $controller->show($request, []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testShowWithRoot()
    {
        $requestHandler = $this->createMock(RequestHandler::class);
        $session = $this->createMock(SessionInterface::class);
        $email = $this->createMock(EmailInterface::class);
        $captcha = $this->createMock(Captcha::class);

        $expectedResponse = 'Rendered HTML Root Content';

        $requestHandler->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $requestHandler->expects($this->once())
            ->method('showRoot')
            ->willReturn(new Response($expectedResponse));

        $request = new Request();
        $controller = new ContactController($requestHandler, $session, $email, $captcha);
        $response = $controller->show($request, []);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedResponse, $response->getContent());
    }
}
