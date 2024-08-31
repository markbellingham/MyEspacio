<?php

declare(strict_types=1);

namespace MyEspacio\User\Application;

use MyEspacio\Framework\Messages\EmailMessageInterface;
use MyEspacio\User\Domain\User;

interface LoginEmailMessageInterface extends EmailMessageInterface
{
    public function assemble(User $user): void;
}
