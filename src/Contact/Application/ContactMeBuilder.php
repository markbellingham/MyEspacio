<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Application;

use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\Framework\Sanitization\SafeHtmlSanitizerInterface;

final readonly class ContactMeBuilder implements ContactMeBuilderInterface
{
    public function __construct(
        private SafeHtmlSanitizerInterface $sanitizer,
        private TemplateRendererFactoryInterface $templateRendererFactory,
    ) {
    }

    /**
     * @throws InvalidEmailException
     */
    public function build(DataSet $data): ContactMeMessage
    {
        $message = $this->sanitizer->sanitizeContactMeMessage($data->string('message'));
        if (trim($message) === '') {
            throw new InvalidEmailException('Message cannot be empty.');
        }

        $templateRenderer = $this->templateRendererFactory->create();
        $html = $templateRenderer->render('contact/ContactMeEmail.html.twig', [
            'emailAddress' => $data->string('emailAddress'),
            'name' => $data->string('name'),
            'subject' => $data->string('subject'),
            'message' => $message,
        ]);

        return new ContactMeMessage(
            emailAddress: $data->string('emailAddress'),
            name: $data->string('name'),
            subject: $data->string('subject'),
            message: $html,
            captchaIconId: $data->intNull('captcha1'),
            description: $data->string('description')
        );
    }
}
