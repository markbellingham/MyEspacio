<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Domain;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Messages\EmailMessage;

final class ContactMeMessage extends EmailMessage
{
    /**
     * @throws InvalidEmailException
     */
    public function __construct(
        protected string $emailAddress,
        protected string $name,
        protected string $subject,
        protected string $message,
        private ?int $captchaIconId,
        private ?string $description
    ) {
        $this->setCaptcha($captchaIconId);
        $this->setDescription($description);
        $this->setEmailAddress($emailAddress);
        $this->setName($name);
        $this->setSubject($subject);
        $this->setMessage($message);
    }

    /**
     * @throws InvalidEmailException
     */
    private function setCaptcha(?int $captcha): void
    {
        $this->captchaIconId = $captcha;
        if ($captcha === null) {
            throw InvalidEmailException::invalidMessage($this->toArray());
        }
    }

    /**
     * @throws InvalidEmailException
     */
    private function setDescription(?string $description): void
    {
        $this->description = $description;
        if (
            $description === null ||
            strlen($description) > 0
        ) {
            throw InvalidEmailException::invalidMessage($this->toArray());
        }
    }

    /** @return array<string, string> */
    private function toArray(): array
    {
        return [
            'emailAddress' => $this->emailAddress,
            'message' => $this->message,
            'name' => $this->name,
            'subject' => $this->subject,
            'captchaIconId' => (string) ($this->captchaIconId ?? 'null'),
            'description' => $this->description ?? 'null',
        ];
    }
}
