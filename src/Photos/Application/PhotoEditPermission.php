<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Application;

use MyEspacio\Framework\PermissionInterface;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRole;

final class PhotoEditPermission implements PermissionInterface
{
    public static function isAllowedFor(User $user): bool
    {
        return $user->getRole() === UserRole::Admin;
    }
}
