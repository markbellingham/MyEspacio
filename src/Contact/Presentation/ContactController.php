<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Presentation;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Messages\EmailInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ContactController
{
    public const CAPTCHA_ICONS_QUANTITY = 7;

    public function __construct(
        private readonly RequestHandler $requestHandler,
        private readonly SessionInterface $session,
        private readonly EmailInterface $email,
        private readonly Captcha $captcha
    ) {
    }

    public function show(Request $request, array $vars): Response
    {
        $redirect = $this->requestHandler->validate($request);
        if ($redirect) {
            return $this->requestHandler->showRoot($request, $vars);
        }

        $icons = $this->captcha->getIcons(self::CAPTCHA_ICONS_QUANTITY);
        $this->session->set('contactIcons', $icons->toArray());

        $captcha1 = $this->captcha->getSelectedIcon();
        $captcha2 = $this->captcha->getEncryptedIcon();

        return $this->requestHandler->sendResponse(
            [
                'icons' => $icons,
                'captcha1' => $this->captcha->getSelectedIcon(),
                'captcha2' => $this->captcha->getEncryptedIcon()
            ],
            'contact/Contact.html.twig'
        );
    }
}
