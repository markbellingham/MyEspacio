<?php

declare(strict_types=1);

namespace MyEspacio\User\Infrastructure\MySql;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function getUserByEmailAddress(string $email): ?User
    {
        $result = $this->db->fetchOne(
            'SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE email = :email',
            [
                'email' => $email
            ]
        );

        if ($result) {
            $result = new DataSet($result);
            return User::createFromDataSet($result);
        }
        return null;
    }

    public function getUserByPhoneNumber(string $phone): ?User
    {
        $result = $this->db->fetchOne(
            'SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE phone = :phoneNumber',
            [
                'phoneNumber' => $phone
            ]
        );

        if ($result) {
            $result = new DataSet($result);
            return User::createFromDataSet($result);
        }
        return null;
    }

    public function getUserFromMagicLink(string $magicLink): ?User
    {
        $result = $this->db->fetchOne(
            'SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE magic_link = :magicLink',
            [
                'magicLink' => $magicLink
            ]
        );
        if ($result) {
            $result = new DataSet($result);
            return User::createFromDataSet($result);
        }
        return null;
    }

    public function saveLoginDetails(User $user): bool
    {
        $stmt = $this->db->run(
            'UPDATE project.users 
            SET login_attempts = login_attempts + 1, login_date = :loginDate, magic_link = :magicLink, phone_code = :phoneCode 
            WHERE id = :id',
            [
                'loginDate' => date('Y-m-d H:i:s'),
                'magicLink' => $user->getMagicLink(),
                'phoneCode' => $user->getPhoneCode(),
                'id' => $user->getId()
            ]
        );
        return $this->db->statementHasErrors($stmt) === false && $stmt->rowCount() === 1;
    }

    public function getAnonymousUser(): User
    {
        return new User(
            email: 'website@markbellingham.uk',
            uuid: '95c7cdac-6a6f-44ca-a28f-fc62ef61405d',
            name: 'Anonymous',
            phone: null,
            loginAttempts: null,
            loginDate: null,
            magicLink: null,
            phoneCode: null,
            passcodeRoute: 'email',
            id: 1
        );
    }
}
