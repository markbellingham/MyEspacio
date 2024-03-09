<?php

declare(strict_types=1);

namespace MyEspacio\User\Infrastructure;

use MyEspacio\User\Domain\User;

interface UserRepository
{
    public function getUserByLoginValues(string $field, string $value): ?User;

    public function saveLoginDetails(User $user): bool;
}
