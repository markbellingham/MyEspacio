<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Presentation;

use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Messages\EmailInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ContactController
{
    public const CAPTCHA_ICONS_QUANTITY = 7;

    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
        private readonly SessionInterface $session,
        private readonly EmailInterface $email,
        private readonly CaptchaInterface $captcha,
        private readonly LanguageReader $languageReader,
    ) {
    }

    /**
     * @param Request $request
     * @param array<string, string> $vars
     * @return Response
     */
    public function show(Request $request, array $vars): Response
    {
        $valid = $this->requestHandler->validate($request);
        if ($valid === false) {
            return $this->requestHandler->showRoot($request, $vars);
        }

        $icons = $this->captcha->getIcons(self::CAPTCHA_ICONS_QUANTITY);
        $this->session->set('contactIcons', $icons->toArray());

        return $this->requestHandler->sendResponse(
            [
                'icons' => $icons,
                'captcha1' => $this->captcha->getSelectedIcon(),
                'captcha2' => $this->captcha->getEncryptedIcon()
            ],
            'contact/Contact.html.twig',
        );
    }

    public function sendMessage(Request $request): Response
    {
        $vars = new DataSet(json_decode($request->getContent(), true));

        $valid = $this->requestHandler->validate($request);
        if ($valid === false) {
            return $this->requestHandler->showRoot($request, $vars->toArray());
        }

        if ($this->captcha->validate($vars->intNull('captcha1'), $vars->stringNull('captcha2')) === false) {
            return $this->requestHandler->sendResponse(
                data: [
                    'error' => $this->languageReader->getTranslationText(
                        $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                        'contact.bad_message'
                    ),
                ],
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $emailMessage = new ContactMeMessage(
                emailAddress: $vars->stringNull('emailAddress'),
                name: $vars->stringNull('name'),
                subject: $vars->stringNull('subject'),
                message: $vars->stringNull('message'),
                captchaIconId: $vars->intNull('captcha1'),
                description: $vars->string('description')
            );
        } catch (InvalidEmailException $e) {
            return $this->requestHandler->sendResponse(
                data: ['error' => $this->languageReader->getTranslationText(
                    $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    'contact.form_fail'
                )],
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        if ($this->email->send($emailMessage) === false) {
            return $this->requestHandler->sendResponse(
                data: ['error' => $this->languageReader->getTranslationText(
                    $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                    'contact.email_fail'
                )],
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->requestHandler->sendResponse([
            'captcha' => $this->captcha,
            'message' => $this->languageReader->getTranslationText(
                $this->requestHandler->getTranslationIdentifier($request, 'messages'),
                'contact.email_success'
            ),
        ]);
    }
}
