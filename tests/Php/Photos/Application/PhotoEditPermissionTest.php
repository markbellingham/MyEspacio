<?php

declare(strict_types=1);

namespace Tests\Php\Photos\Application;

use MyEspacio\Photos\Application\PhotoEditPermission;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Php\Factory\UserTestFactory;

final class PhotoEditPermissionTest extends TestCase
{
    #[DataProvider('isAllowedForDataProvider')]
    public function testIsAllowedFor(
        User $user,
        bool $expected,
    ): void {
        $this->assertSame(
            $expected,
            PhotoEditPermission::isAllowedFor($user),
        );
    }

    /** @return array<string, array<string, User|bool>> */
    public static function isAllowedForDataProvider(): array
    {
        return [
            'admin user' => [
                'user' => UserTestFactory::create(
                    role: UserRole::User,
                ),
                'expected' => false,
            ],
            'regular user' => [
                'user' => UserTestFactory::create(
                    role: UserRole::Admin,
                ),
                'expected' => true,
            ],
        ];
    }
}
