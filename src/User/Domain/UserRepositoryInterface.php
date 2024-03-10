<?php

declare(strict_types=1);

namespace MyEspacio\User\Domain;

interface UserRepositoryInterface
{
    public function getUserByLoginValues(string $field, string $value): ?User;

    public function getUserFromMagicLink(string $magicLink): ?User;

    public function saveLoginDetails(User $user): bool;
}
