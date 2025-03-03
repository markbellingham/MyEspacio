<?php

declare(strict_types=1);

namespace Tests\User\Domain;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use MyEspacio\Framework\DataSet;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UserTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: 'email',
            id: 1
        );

        $this->assertEquals('mail@example.com', $user->getEmail());
        $this->assertEquals('Mark', $user->getName());
        $this->assertEquals('f47ac10b-58cc-4372-a567-0e02b2c3d479', $user->getUuid());
        $this->assertSame(1, $user->getLoginAttempts());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getLoginDate());
        $this->assertEquals('2024-03-02 15:26:00', $user->getLoginDateString());
        $this->assertEquals('02-03-2024 @ 15:26', $user->getLoginDateString('d-m-Y @ H:i'));
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $user->getMagicLink());
        $this->assertEquals('9bR3xZ', $user->getPhoneCode());
        $this->assertEquals('email', $user->getPasscodeRoute());
        $this->assertEquals('01234567890', $user->getPhone());
        $this->assertSame(1, $user->getId());

        $this->assertEquals(
            [
                'name' => 'Mark',
                'uuid' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            ],
            $user->jsonSerialize()
        );
    }

    public function testDefaultValues(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );

        $this->assertNull($user->getLoginAttempts());
        $this->assertNull($user->getLoginDate());
        $this->assertNull($user->getMagicLink());
        $this->assertNull($user->getPhoneCode());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getId());
        $this->assertEquals('email', $user->getPasscodeRoute());
        $this->assertEquals('Anonymous', $user->getName());
    }

    public function testSetters(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );

        $user->setUuid(Uuid::fromString('a84e4c4f-110d-4f7f-8362-1d592aa8433e'));
        $this->assertEquals(
            'a84e4c4f-110d-4f7f-8362-1d592aa8433e',
            $user->getUuid()
        );

        $user->setName('Mark Bellingham');
        $this->assertEquals('Mark Bellingham', $user->getName());

        $user->setEmail('mail@example.com');
        $this->assertEquals('mail@example.com', $user->getEmail());

        $user->setLoginAttempts(3);
        $this->assertSame(3, $user->getLoginAttempts());

        $user->setLoginDate('2024-03-03 20:42');
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getLoginDate());

        $user->setPhone('01234567890');
        $this->assertEquals('01234567890', $user->getPhone());

        $user->setIsLoggedIn(true);
        $this->assertTrue($user->isLoggedIn());

        $user->setId(1234);
        $this->assertSame(1234, $user->getId());

        $user->setMagicLink('550e8400-e29b-41d4-a716-446655440000');
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $user->getMagicLink());

        $user->setPhoneCode('abc123');
        $this->assertEquals('abc123', $user->getPhoneCode());

        $user->setPasscodeRoute('phone');
        $this->assertEquals('phone', $user->getPasscodeRoute());
    }

    public function testNonDefaultNullValues(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: 'email',
            id: 1
        );

        $user->setPhone(null);
        $this->assertNull($user->getPhone());

        $user->setMagicLink(null);
        $this->assertNull($user->getMagicLink());

        $user->setPhoneCode(null);
        $this->assertNull($user->getPhoneCode());
    }

    public function testUuidException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID');

        new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('Invalid UUID'),
            name: 'Anonymous'
        );
    }

    public function testEmailException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');

        $user = new User(
            email: 'Invalid email',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );
    }

    public function testPasscodeRouteException(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Passcode route must be one of email, phone');
        $user->setPasscodeRoute('Careless whispers');
    }

    public function testLoginDateException(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not create date instance');
        $user->setLoginDate('Invalid date');
    }

    public function testCreateFromDataset(): void
    {
        $data = new DataSet([
            'email' => 'mail@example.com',
            'uuid' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            'name' => 'Mark',
            'phone' => '01234567890',
            'login_attempts' => '1',
            'login_date' => '2024-03-02 15:26:00',
            'magic_link' => '550e8400-e29b-41d4-a716-446655440000',
            'phone_code' => '9bR3xZ',
            'passcode_route' => 'email',
            'id' => '1'
        ]);
        $user = User::createFromDataSet($data);

        $this->assertEquals('mail@example.com', $user->getEmail());
        $this->assertEquals('Mark', $user->getName());
        $this->assertEquals('f47ac10b-58cc-4372-a567-0e02b2c3d479', $user->getUuid()->toString());
        $this->assertSame(1, $user->getLoginAttempts());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getLoginDate());
        $this->assertEquals('2024-03-02 15:26:00', $user->getLoginDateString());
        $this->assertEquals('02-03-2024 @ 15:26', $user->getLoginDateString('d-m-Y @ H:i'));
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $user->getMagicLink());
        $this->assertEquals('9bR3xZ', $user->getPhoneCode());
        $this->assertEquals('email', $user->getPasscodeRoute());
        $this->assertEquals('01234567890', $user->getPhone());
        $this->assertSame(1, $user->getId());
    }
}
