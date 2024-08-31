<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

interface EmailInterface
{
    public function send(EmailMessageInterface $emailMessage): bool;
}
