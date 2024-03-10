<?php

declare(strict_types=1);

namespace Tests\User\Infrastructure;

use DateTimeImmutable;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Infrastructure\MysqlUserRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class MysqlUserRepositoryTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User(
            email: 'mail@example.com',
            uuid: 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            name: 'Mark',
            phone: '01234567890',
            loginAttempts: 1,
            loginDate: new DateTimeImmutable('2024-03-02 15:26:00'),
            magicLink: '550e8400-e29b-41d4-a716-446655440000',
            phoneCode: '9bR3xZ',
            passcodeRoute: 'email',
            id: 1
        );
    }

    public function testGetUserByLoginValues()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                "SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE email = :field",
                [
                    'field' => $this->user->getEmail()
                ]
            )
            ->willReturn(
                [
                    'email' => 'mail@example.com',
                    'uuid' => 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
                    'name' => 'Mark',
                    'phone' => '01234567890',
                    'login_attempts' => '0',
                    'login_date' => null,
                    'magic_link' => null,
                    'phone_code' => null,
                    'passcode_route' => 'email',
                    'id' => '1'
                ]
            );

        $repository = new MysqlUserRepository($db);
        $result = $repository->getUserByLoginValues('email', $this->user->getEmail());

        $this->assertInstanceOf(User::class, $result);
    }

    public function testGetUserByLoginValuesNotFound()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                "SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE email = :field",
                [
                    'field' => $this->user->getEmail()
                ]
            )
            ->willReturn(null);

        $repository = new MysqlUserRepository($db);
        $result = $repository->getUserByLoginValues('email', $this->user->getEmail());

        $this->assertNull($result);
    }

    public function testGetUserByLoginValuesWrongLoginType()
    {
        $db = $this->createMock(Connection::class);

        $repository = new MysqlUserRepository($db);
        $result = $repository->getUserByLoginValues('wrong value', $this->user->getEmail());

        $this->assertNull($result);
    }

    public function testGetUserFromMagicLink()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE magic_link = :magicLink',
                [
                    'magicLink' => $this->user->getMagicLink()
                ]
            )
            ->willreturn(
                [
                    'email' => 'mail@example.com',
                    'uuid' => 'ed459e09-0b90-4d57-b6b2-f5d70d0ff60c',
                    'name' => 'Mark',
                    'phone' => '01234567890',
                    'login_attempts' => '0',
                    'login_date' => null,
                    'magic_link' => 'c52616f0-82fa-4b8f-bb2a-82a012a0a0f7',
                    'phone_code' => 'abc123',
                    'passcode_route' => 'email',
                    'id' => '1'
                ]
            );

        $repository = new MysqlUserRepository($db);
        $result = $repository->getUserFromMagicLink($this->user->getMagicLink());

        $this->assertInstanceOf(User::class, $result);
    }

    public function testGetUserFromMagicLinkNotFound()
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->once())
            ->method('fetchOne')
            ->with(
                'SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE magic_link = :magicLink',
                [
                    'magicLink' => $this->user->getMagicLink()
                ]
            )
            ->willreturn(null);

        $repository = new MysqlUserRepository($db);
        $result = $repository->getUserFromMagicLink($this->user->getMagicLink());

        $this->assertNull($result);
    }

    public function testSaveLoginDetails()
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

        $repository = new MysqlUserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertTrue($result);
    }

    public function testSaveLoginDetailsUserNotFound()
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

        $repository = new MysqlUserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertFalse($result);
    }

    public function testSaveLoginDetailsDatabaseError()
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

        $repository = new MysqlUserRepository($db);
        $result = $repository->saveLoginDetails($this->user);

        $this->assertFalse($result);
    }
}
