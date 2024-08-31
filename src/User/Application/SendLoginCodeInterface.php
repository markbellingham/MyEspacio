<?php

declare(strict_types=1);

namespace MyEspacio\User\Application;

use MyEspacio\User\Domain\User;

interface SendLoginCodeInterface
{
    public function generateCode(User $user): User;

    public function sendTo(User $user): bool;
}
