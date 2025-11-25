<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Presentation;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Messages\EmailInterface;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ContactController extends BaseController
{
    public const int CAPTCHA_ICONS_QUANTITY = 7;

    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
        private readonly SessionInterface $session,
        private readonly EmailInterface $email,
        private readonly CaptchaInterface $captcha
    ) {
    }

    #[Route('/contact', HttpMethod::GET)]
    public function show(Request $request): Response
    {
        $valid = $this->requestHandler->validate($request);

        $icons = $this->captcha->getIcons(self::CAPTCHA_ICONS_QUANTITY);
        $this->session->set('contactIcons', $icons->toArray());

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'icons' => $icons,
                    'captcha1' => $this->captcha->getSelectedIcon(),
                    'captcha2' => $this->captcha->getEncryptedIcon()
                ],
                template: 'contact/Contact.html.twig',
            )
        );
    }

    #[Route('/contact/send', HttpMethod::POST)]
    public function sendMessage(Request $request): Response
    {
        $vars = new DataSet($request->request->all());

        $valid = $this->requestHandler->validate($request);

        if ($this->captcha->validate($vars->intNull('captcha1'), $vars->stringNull('captcha2')) === false) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'contact.bad_message'
                )
            );
        }

        try {
            $emailMessage = new ContactMeMessage(
                emailAddress: $vars->string('emailAddress'),
                name: $vars->string('name'),
                subject: $vars->string('subject'),
                message: $vars->string('message'),
                captchaIconId: $vars->intNull('captcha1'),
                description: $vars->string('description')
            );
        } catch (InvalidEmailException $e) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'contact.form_fail'
                )
            );
        }

        if ($this->email->send($emailMessage) === false) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                    translationKey: 'contact.email_fail'
                )
            );
        }

        $icons = $this->captcha->getIcons(self::CAPTCHA_ICONS_QUANTITY);
        $this->session->set('contactIcons', $icons->toArray());

        return $this->requestHandler->sendResponse(
            new ResponseData(
                data: [
                    'icons' => $icons,
                    'captcha1' => $this->captcha->getSelectedIcon(),
                    'captcha2' => $this->captcha->getEncryptedIcon()
                ],
                translationKey: 'contact.email_success'
            )
        );
    }
}
