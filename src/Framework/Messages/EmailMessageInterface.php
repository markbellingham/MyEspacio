<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

interface EmailMessageInterface
{
    public function getEmailAddress(): string;
    public function getMessage(): string;
    public function getName(): string;
    public function getSubject(): string;
}
