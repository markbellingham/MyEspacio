<?php

declare(strict_types=1);

namespace Tests\Php\Factory;

use DateTimeImmutable;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRole;
use Ramsey\Uuid\Uuid;

final class UserTestFactory
{
    public static function create(
        string $email = 'mail@example.com',
        string $uuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
        string $name = 'Mark',
        string $phone = '01234567890',
        int $loginAttempts = 1,
        DateTimeImmutable $loginDate = new DateTimeImmutable('2024-03-02 15:26:00'),
        string $magicLink = '550e8400-e29b-41d4-a716-446655440000',
        string $phoneCode = '9bR3xZ',
        PasscodeRoute $passcodeRoute = PasscodeRoute::Email,
        UserRole $role = UserRole::User,
        int $id = 1
    ): User {
        return new User(
            $email,
            Uuid::fromString($uuid),
            $name,
            $phone,
            $loginAttempts,
            $loginDate,
            $magicLink,
            $phoneCode,
            $passcodeRoute,
            $role,
            $id,
        );
    }
}
