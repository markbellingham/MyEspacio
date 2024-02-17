<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

interface Email
{
    public function send(EmailMessage $emailMessage): bool;
}
