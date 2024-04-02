<?php

declare(strict_types=1);

namespace MyEspacio\User\Domain;

interface UserRepositoryInterface
{
    public function getUserByEmailAddress(string $email): ?User;

    public function getUserByPhoneNumber(string $phone): ?User;

    public function getUserFromMagicLink(string $magicLink): ?User;

    public function saveLoginDetails(User $user): bool;
}
