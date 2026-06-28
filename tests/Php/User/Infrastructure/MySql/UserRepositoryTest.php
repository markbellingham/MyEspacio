<?php

declare(strict_types=1);

namespace Tests\Php\User\Infrastructure\MySql;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\User\Domain\PasscodeRoute;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRole;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UserRepositoryTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User(
            email: 'mail@example.com',
            uuid: Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'),
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: PasscodeRoute::Email,
            role: UserRole::User,
            id: 1
        );
    }

    /** @param null|array<string, string> $databaseResult */
    #[DataProvider('getUserByEmailAddressDataProvider')]
    public function testGetUserByEmailAddress(
        string $emailAddress,
        ?array $databaseResult,
        ?User $expectedResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT id, 
                    name, 
                    uuid, 
                    email, 
                    phone, 
                    passcode_route, 
                    login_attempts, 
                    login_date, 
                    magic_link, 
                    phone_code,
                    role
                FROM project.users
                WHERE email = :email',
                [
                    'email' => $emailAddress,
                ]
            )
            ->willReturn($databaseResult);

        $repository = new UserRepository($db);
        $actualResult = $repository->getUserByEmailAddress($emailAddress);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function getUserByEmailAddressDataProvider(): array
    {
        return [
            'test_null' => [
                'emailAddress' => 'mail@example.com',
                'databaseResult' => [
                    'email' => 'mail@example.com',
                    'uuid' => 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
                    'name' => 'Mark',
                    'phone' => null,
                    'login_attempts' => '0',
                    'login_date' => null,
                    'magic_link' => null,
                    'phone_code' => null,
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'mail@example.com',
                    uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
                    name: 'Mark',
                    phone: null,
                    loginAttempts: 0,
                    loginDate: null,
                    magicLink: null,
                    phoneCode: null,
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test_populated' => [
                'emailAddress' => 'test@mail.tld',
                'databaseResult' => [
                    'email' => 'test@mail.tld',
                    'uuid' => 'cd6ad707-53f9-458f-81f4-ea580968bc11',
                    'name' => 'James',
                    'phone' => '07890123456',
                    'login_attempts' => '2',
                    'login_date' => '2026-05-28 14:36:00',
                    'magic_link' => '550e8400-e29b-41d4-a716-446655440000',
                    'phone_code' => '9bR3xZ',
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'test@mail.tld',
                    uuid: Uuid::fromString('cd6ad707-53f9-458f-81f4-ea580968bc11'),
                    name: 'James',
                    phone: '07890123456',
                    loginAttempts: 2,
                    loginDate: new DateTimeImmutable('2026-05-28T14:36:00+00:00'),
                    magicLink: '550e8400-e29b-41d4-a716-446655440000',
                    phoneCode: '9bR3xZ',
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test_not_found' => [
                'emailAddress' => 'sent-to@home.address',
                'databaseResult' => null,
                'expectedResult' => null,
            ],
        ];
    }

    /** @param null|array<string, string> $databaseResult */
    #[DataProvider('getUserByPhoneNumberDataProvider')]
    public function testGetUserByPhoneNumber(
        string $phoneNumber,
        ?array $databaseResult,
        ?User $expectedResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT 
                    id, 
                    name, 
                    uuid, 
                    email, 
                    phone, 
                    passcode_route, 
                    login_attempts, 
                    login_date, 
                    magic_link, 
                    phone_code,
                    role
                FROM project.users
                WHERE phone = :phoneNumber',
                [
                    'phoneNumber' => $phoneNumber,
                ]
            )
            ->willReturn($databaseResult);

        $repository = new UserRepository($db);
        $actualResult = $repository->getUserByPhoneNumber($phoneNumber);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function getUserByPhoneNumberDataProvider(): array
    {
        return [
            'test_null' => [
                'phoneNumber' => '01234567890',
                'databaseResult' => [
                    'email' => 'mail@example.com',
                    'uuid' => 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
                    'name' => 'Mark',
                    'phone' => '01234567890',
                    'login_attempts' => '0',
                    'login_date' => null,
                    'magic_link' => null,
                    'phone_code' => null,
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'mail@example.com',
                    uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
                    name: 'Mark',
                    phone: '01234567890',
                    loginAttempts: 0,
                    loginDate: null,
                    magicLink: null,
                    phoneCode: null,
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test_populateed' => [
                'phoneNumber' => '07890123456',
                'databaseResult' => [
                    'email' => 'test@mail.tld',
                    'uuid' => 'cd6ad707-53f9-458f-81f4-ea580968bc11',
                    'name' => 'James',
                    'phone' => '07890123456',
                    'login_attempts' => '2',
                    'login_date' => '2026-05-28 14:36:00',
                    'magic_link' => '550e8400-e29b-41d4-a716-446655440000',
                    'phone_code' => '9bR3xZ',
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'test@mail.tld',
                    uuid: Uuid::fromString('cd6ad707-53f9-458f-81f4-ea580968bc11'),
                    name: 'James',
                    phone: '07890123456',
                    loginAttempts: 2,
                    loginDate: new DateTimeImmutable('2026-05-28T14:36:00+00:00'),
                    magicLink: '550e8400-e29b-41d4-a716-446655440000',
                    phoneCode: '9bR3xZ',
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test|_not_found' => [
                'phoneNumber' => '',
                'databaseResult' => null,
                'expectedResult' => null,
            ],
        ];
    }

    /** @param null|array<string, string> $databaseResult */
    #[DataProvider('getUserFromMagicLinkDataProvider')]
    public function testGetUserFromMagicLink(
        string $magicLink,
        ?array $databaseResult,
        ?User $expectedResult,
    ): void {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT 
                    id, 
                    name, 
                    uuid, 
                    email, 
                    phone, 
                    passcode_route, 
                    login_attempts, 
                    login_date, 
                    magic_link, 
                    phone_code,
                    role
                FROM project.users
                WHERE magic_link = :magicLink',
                [
                    'magicLink' => $magicLink,
                ]
            )
            ->willreturn($databaseResult);

        $repository = new UserRepository($db);
        $actualResult = $repository->getUserFromMagicLink($magicLink);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function getUserFromMagicLinkDataProvider(): array
    {
        return [
            'test_null' => [
                'magicLink' => 'c52616f0-82fa-4b8f-bb2a-82a012a0a0f7',
                'databaseResult' => [
                    'email' => 'mail@example.com',
                    'uuid' => 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
                    'name' => 'Mark',
                    'phone' => null,
                    'login_attempts' => '0',
                    'login_date' => null,
                    'magic_link' => 'c52616f0-82fa-4b8f-bb2a-82a012a0a0f7',
                    'phone_code' => null,
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'mail@example.com',
                    uuid: Uuid::fromString('ed459e09-0b90-4d57-b6b2-f5d70d0ff60c'),
                    name: 'Mark',
                    phone: null,
                    loginAttempts: 0,
                    loginDate: null,
                    magicLink: 'c52616f0-82fa-4b8f-bb2a-82a012a0a0f7',
                    phoneCode: null,
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test_populated' => [
                'magicLink' => '1c08b72a-6bf0-4084-a171-1c6276e928fd',
                'databaseResult' => [
                    'email' => 'test@mail.tld',
                    'uuid' => 'cd6ad707-53f9-458f-81f4-ea580968bc11',
                    'name' => 'James',
                    'phone' => '07890123456',
                    'login_attempts' => '2',
                    'login_date' => '2026-05-28 14:36:00',
                    'magic_link' => '1c08b72a-6bf0-4084-a171-1c6276e928fd',
                    'phone_code' => '9bR3xZ',
                    'passcode_route' => 'email',
                    'role' => 'user',
                    'id' => '1'
                ],
                'expectedResult' => new User(
                    email: 'test@mail.tld',
                    uuid: Uuid::fromString('cd6ad707-53f9-458f-81f4-ea580968bc11'),
                    name: 'James',
                    phone: '07890123456',
                    loginAttempts: 2,
                    loginDate: new DateTimeImmutable('2026-05-28T14:36:00+00:00'),
                    magicLink: '1c08b72a-6bf0-4084-a171-1c6276e928fd',
                    phoneCode: '9bR3xZ',
                    passcodeRoute: PasscodeRoute::Email,
                    role: UserRole::User,
                    id: 1
                ),
            ],
            'test_not_found' => [
                'magicLink' => '82104798-07b0-4968-a9e4-6329d4bc329e',
                'databaseResult' => null,
                'expectedResult' => null,
            ],
        ];
    }

    public function testSaveLoginDetails(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'UPDATE project.users 
            SET login_attempts = login_attempts + 1, login_date = :loginDate, magic_link = :magicLink, phone_code = :phoneCode 
            WHERE id = :id',
                [
                    'loginDate' => date('Y-m-d H:i:s'),
                    'magicLink' => $this->user->getMagicLink(),
                    'phoneCode' => $this->user->getPhoneCode(),
                    'id' => $this->user->getId()
                ]
            )
            ->willReturn($stmt);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->willReturn(false);

        $repository = new UserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertTrue($result);
    }

    public function testSaveLoginDetailsUserNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'UPDATE project.users 
            SET login_attempts = login_attempts + 1, login_date = :loginDate, magic_link = :magicLink, phone_code = :phoneCode 
            WHERE id = :id',
                [
                    'loginDate' => date('Y-m-d H:i:s'),
                    'magicLink' => $this->user->getMagicLink(),
                    'phoneCode' => $this->user->getPhoneCode(),
                    'id' => $this->user->getId()
                ]
            )
            ->willReturn($stmt);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->willReturn(false);

        $repository = new UserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertFalse($result);
    }

    public function testSaveLoginDetailsDatabaseError(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('run')
            ->with(
                'UPDATE project.users 
            SET login_attempts = login_attempts + 1, login_date = :loginDate, magic_link = :magicLink, phone_code = :phoneCode 
            WHERE id = :id',
                [
                    'loginDate' => date('Y-m-d H:i:s'),
                    'magicLink' => $this->user->getMagicLink(),
                    'phoneCode' => $this->user->getPhoneCode(),
                    'id' => $this->user->getId()
                ]
            )
            ->willReturn($stmt);
        $db->expects($this->once())
            ->method('statementHasErrors')
            ->willReturn(true);

        $repository = new UserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertFalse($result);
    }

    public function testGetAnonymousUser(): void
    {
        $result = UserRepository::getAnonymousUser();
        $this->assertInstanceOf(User::class, $result);
        $this->assertSame(1, $result->getId());
    }
}
