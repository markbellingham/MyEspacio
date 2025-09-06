<?php

declare(strict_types=1);

namespace Tests\Php\User\Domain;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use MyEspacio\Framework\DataSet;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserTest extends TestCase
{
    /** @param array<string, string> $jsonSerialized */
    #[DataProvider('modelDataProvider')]
    public function testModel(
        string $email,
        UuidInterface $uuid,
        string $name,
        string $phone,
        int $loginAttempts,
        DateTimeImmutable $loginDate,
        string $magicLink,
        string $phoneCode,
        PasscodeRoute $passcodeRoute,
        int $id,
        array $jsonSerialized,
    ): void {
        $user = new User(
            $email,
            $uuid,
            $name,
            $phone,
            $loginAttempts,
            $loginDate,
            $magicLink,
            $phoneCode,
            $passcodeRoute,
            $id,
        );

        $this->assertSame($email, $user->getEmail());
        $this->assertSame($name, $user->getName());
        $this->assertSame($uuid, $user->getUuid());
        $this->assertSame($loginAttempts, $user->getLoginAttempts());
        $this->assertSame($loginDate, $user->getLoginDate());
        $this->assertSame($magicLink, $user->getMagicLink());
        $this->assertSame($phoneCode, $user->getPhoneCode());
        $this->assertSame($passcodeRoute, $user->getPasscodeRoute());
        $this->assertSame($phone, $user->getPhone());
        $this->assertSame($id, $user->getId());

        $this->assertEquals($jsonSerialized, json_decode((string) json_encode($user), true));
    }

    /** @return array<string, array<string, mixed>> */
    public static function modelDataProvider(): array
    {
        return [
            'test_1' => [
                'email' => 'mail@example.com',
                'uuid' => Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
                'name' => 'Mark',
                'phone' => '01234567890',
                'loginAttempts' => 1,
                'loginDate' => new DateTimeImmutable('2024-03-02 15:26:00'),
                'magicLink' => '550e8400-e29b-41d4-a716-446655440000',
                'phoneCode' => '9bR3xZ',
                'passcodeRoute' => PasscodeRoute::Email,
                'id' => 1,
                'jsonSerialized' => [
                    'name' => 'Mark',
                    'uuid' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                ],
            ],
            'test_2' => [
                'email' => 'sarah.johnson@testdomain.org',
                'uuid' => Uuid::fromString('a8b9c2d3-4e5f-6789-1234-567890abcdef'),
                'name' => 'Sarah',
                'phone' => '07891234567',
                'loginAttempts' => 3,
                'loginDate' => new DateTimeImmutable('2024-08-15 09:42:17'),
                'magicLink' => 'c4a8f9e2-7b3d-4c1a-9f8e-2d6b5a4c3e1f',
                'phoneCode' => '4kT7mP',
                'passcodeRoute' => PasscodeRoute::Phone,
                'id' => 2,
                'jsonSerialized' => [
                    'name' => 'Sarah',
                    'uuid' => 'a8b9c2d3-4e5f-6789-1234-567890abcdef',
                ]
            ],
        ];
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
        $this->assertEquals(PasscodeRoute::Email, $user->getPasscodeRoute());
        $this->assertEquals('Anonymous', $user->getName());
    }

    #[DataProvider('settersDataProvider')]
    public function testSetters(
        UuidInterface $uuid,
        string $name,
        string $email,
        int $loginAttempts,
        DateTimeImmutable $loginDate,
        string $phone,
        bool $isLoggedIn,
        int $id,
        string $magicLink,
        string $phoneCode,
        PasscodeRoute $passcodeRoute,
    ): void {
        $user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );

        $this->assertEquals(Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'), $user->getUuid());
        $user->setUuid($uuid);
        $this->assertEquals($uuid, $user->getUuid());

        $this->assertSame('Anonymous', $user->getName());
        $user->setName($name);
        $this->assertEquals($name, $user->getName());

        $this->assertSame('mail@example.com', $user->getEmail());
        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());

        $this->assertNull($user->getLoginAttempts());
        $user->setLoginAttempts($loginAttempts);
        $this->assertSame($loginAttempts, $user->getLoginAttempts());

        $this->assertNull($user->getLoginDate());
        $user->setLoginDate($loginDate);
        $this->assertSame($loginDate, $user->getLoginDate());

        $this->assertNull($user->getPhone());
        $user->setPhone($phone);
        $this->assertEquals($phone, $user->getPhone());

        $this->assertFalse($user->isLoggedIn());
        $user->setIsLoggedIn($isLoggedIn);
        $this->assertSame($isLoggedIn, $user->isLoggedIn());

        $this->assertNull($user->getId());
        $user->setId($id);
        $this->assertSame($id, $user->getId());

        $this->assertNull($user->getMagicLink());
        $user->setMagicLink($magicLink);
        $this->assertEquals($magicLink, $user->getMagicLink());

        $this->assertNull($user->getPhoneCode());
        $user->setPhoneCode($phoneCode);
        $this->assertEquals($phoneCode, $user->getPhoneCode());

        $this->assertEquals(PasscodeRoute::Email, $user->getPasscodeRoute());
        $user->setPasscodeRoute($passcodeRoute);
        $this->assertEquals($passcodeRoute, $user->getPasscodeRoute());
    }

    /** @return array<string, array<string, mixed>> */
    public static function settersDataProvider(): array
    {
        return [
            'test_1' => [
                'uuid' => Uuid::fromString('e8964cd1-1541-4bbf-9a85-400ed2399145'),
                'name' => 'Mark Bellingham',
                'email' => 'sendTo@domain.tld',
                'loginAttempts' => 3,
                'loginDate' => new DateTimeImmutable('2024-03-02 15:26:00'),
                'phone' => '01234567890',
                'isLoggedIn' => true,
                'id' => 1234,
                'magicLink' => '550e8400-e29b-41d4-a716-446655440000',
                'phoneCode' => 'abc123',
                'passcodeRoute' => PasscodeRoute::Phone,
            ],
            'test_2' => [
                'uuid' => Uuid::fromString('a1b2c3d4-5678-90ab-cdef-1234567890ab'),
                'name' => 'Alice Johnson',
                'email' => 'alice@example.org',
                'loginAttempts' => 0,
                'loginDate' => new DateTimeImmutable('2025-01-15 09:45:30'),
                'phone' => '+441234567890',
                'isLoggedIn' => false,
                'id' => 9876,
                'magicLink' => '123e4567-e89b-12d3-a456-426614174000',
                'phoneCode' => 'xyz789',
                'passcodeRoute' => PasscodeRoute::Email,
            ],
            'test_null' => [
                'uuid' => Uuid::fromString('a1b2c3d4-5678-90ab-cdef-1234567890ab'),
                'name' => 'Alice Johnson',
                'email' => 'alice@example.org',
                'loginAttempts' => 0,
                'loginDate' => new DateTimeImmutable('2025-01-15 09:45:30'),
                'phone' => '+441234567890',
                'isLoggedIn' => false,
                'id' => 9876,
                'magicLink' => '123e4567-e89b-12d3-a456-426614174000',
                'phoneCode' => 'xyz789',
                'passcodeRoute' => PasscodeRoute::Email,
            ]
        ];
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
            passcodeRoute: PasscodeRoute::Email,
            id: 1
        );

        $user->setPhone(null);
        $this->assertNull($user->getPhone());

        $user->setMagicLink(null);
        $this->assertNull($user->getMagicLink());

        $user->setPhoneCode(null);
        $this->assertNull($user->getPhoneCode());
    }

    public function testEmailException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');

        new User(
            email: 'Invalid email',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Anonymous'
        );
    }

    #[DataProvider('createFromDataSetDataProvider')]
    public function testCreateFromDataset(
        DataSet $dataSet,
        User $expectedModel,
    ): void {
        $actualModel = User::createFromDataSet($dataSet);

        $this->assertEquals($expectedModel, $actualModel);
    }

    /** @return array<string, array<string, mixed>> */
    public static function createFromDataSetDataProvider(): array
    {
        return [
            'test_1' => [
                'dataSet' => new DataSet([
                    'email' => 'mail@example.com',
                    'uuid' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                    'name' => 'Mark',
                    'phone' => '01234567890',
                    'login_attempts' => '1',
                    'login_date' => '2024-03-02 15:26:00',
                    'magic_link' => '550e8400-e29b-41d4-a716-446655440000',
                    'phone_code' => '9bR3xZ',
                    'passcode_route' => 'email',
                    'id' => '7'
                ]),
                'expectedModel' => new User(
                    email: 'mail@example.com',
                    uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
                    name: 'Mark',
                    phone: '01234567890',
                    loginAttempts: 1,
                    loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
                    magicLink: '550e8400-e29b-41d4-a716-446655440000',
                    phoneCode: '9bR3xZ',
                    passcodeRoute: PasscodeRoute::Email,
                    id: 7
                ),
            ],
            'test_2' => [
                'dataSet' => new DataSet([
                    'email' => 'joe@bloggs.tld',
                    'uuid' => '206c45b8-9dcc-418c-8efb-8959ad69aaea',
                    'name' => 'Joe',
                    'phone' => '07890123456',
                    'login_attempts' => '1',
                    'login_date' => '2025-08-18 20:33:00',
                    'magic_link' => '5bae1895-cbc7-4e75-8044-db476168decd',
                    'phone_code' => 'abc1234',
                    'passcode_route' => 'phone',
                    'id' => '7'
                ]),
                'expectedModel' => new User(
                    email: 'joe@bloggs.tld',
                    uuid: Uuid::fromString('206c45b8-9dcc-418c-8efb-8959ad69aaea'),
                    name: 'Joe',
                    phone: '07890123456',
                    loginAttempts: 1,
                    loginDate: new DateTimeImmutable('2025-08-18 20:33:00'),
                    magicLink: '5bae1895-cbc7-4e75-8044-db476168decd',
                    phoneCode: 'abc1234',
                    passcodeRoute: PasscodeRoute::Phone,
                    id: 7
                ),
            ],
            'test_null' => [
                'dataSet' => new DataSet([
                    'email' => 'joe@bloggs.tld',
                    'uuid' => '206c45b8-9dcc-418c-8efb-8959ad69aaea',
                    'name' => 'Mark',
                    'phone' => null,
                    'login_attempts' => null,
                    'login_date' => null,
                    'magic_link' => null,
                    'phone_code' => null,
                    'passcode_route' => 'phone',
                    'id' => null
                ]),
                'expectedModel' => new User(
                    email: 'joe@bloggs.tld',
                    uuid: Uuid::fromString('206c45b8-9dcc-418c-8efb-8959ad69aaea'),
                    name: 'Mark',
                    phone: null,
                    loginAttempts: null,
                    loginDate: null,
                    magicLink: null,
                    phoneCode: null,
                    passcodeRoute: PasscodeRoute::Phone,
                    id: null
                ),
            ],
        ];
    }
}
