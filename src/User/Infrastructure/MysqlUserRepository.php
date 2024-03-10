<?php

declare(strict_types=1);

namespace MyEspacio\User\Infrastructure;

use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\DataSet;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Domain\UserRepositoryInterface;

final class MysqlUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function getUserByLoginValues(string $field, string $value): ?User
    {
        if (in_array($field, ['email', 'phone']) === false) {
            return null;
        }

        $result = $this->db->fetchOne(
            "SELECT id, name, uuid, email, phone, passcode_route, login_attempts, login_date, magic_link, phone_code
                FROM project.users
                WHERE $field = :field",
            [
                'field' => $value
            ]
        );
        if ($result) {
            $result = new DataSet($result);
            return $this->createUserInstance($result);
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
            return $this->createUserInstance($result);
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

    private function createUserInstance(DataSet $result): User
    {
        return new User(
            email: $result->string('email'),
            uuid: $result->string('uuid'),
            name: $result->string('name'),
            phone: $result->stringNull('phone'),
            loginAttempts: $result->intNull('login_attempts'),
            loginDate: $result->dateTimeNull('login_date'),
            magicLink: $result->stringNull('magic_link'),
            phoneCode: $result->stringNull('phone_code'),
            passcodeRoute: $result->string('passcode_route'),
            id: $result->int('id')
        );
    }
}
