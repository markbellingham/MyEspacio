<?php

declare(strict_types=1);

namespace MyEspacio\Framework;

use MyEspacio\User\Domain\User;

interface PermissionInterface
{
    public static function isAllowedFor(User $user): bool;
}
