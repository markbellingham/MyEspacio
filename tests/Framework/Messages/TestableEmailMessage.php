<?php

declare(strict_types=1);

namespace Tests\Framework\Messages;

use MyEspacio\Framework\Messages\EmailMessage;

final class TestableEmailMessage extends EmailMessage
{
    public function setProtectedEmailAddress(string $emailAddress): void
    {
        $this->setEmailAddress($emailAddress);
    }

    public function setProtectedMessage(string $message): void
    {
        $this->setMessage($message);
    }

    public function setProtectedName(string $name): void
    {
        $this->setName($name);
    }

    public function setProtectedSubject(string $subject): void
    {
        $this->setSubject($subject);
    }
}
