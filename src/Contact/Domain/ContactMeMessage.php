<?php

declare(strict_types=1);

namespace MyEspacio\Contact\Domain;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Messages\EmailMessage;

final class ContactMeMessage extends EmailMessage
{
    private string $captchaEncrypted;

    /**
     * @throws InvalidEmailException
     */
    public function __construct(
        string $emailAddress,
        string $name,
        string $subject,
        string $message,
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

    private function setCaptcha(?int $captcha): void
    {
        $this->captchaIconId = $captcha;
        if ($captcha === null) {
            throw InvalidEmailException::invalidMessage($this->toArray());
        }
    }

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

    private function toArray(): array
    {
        return get_object_vars($this);
    }
}
