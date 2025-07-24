<?php

declare(strict_types=1);

namespace MyEspacio\User\Domain;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @throws InvalidArgumentException
 */
final class User extends Model
{
    private bool $isLoggedIn = false;

    private const array VALID_PASSCODE_ROUTES = [
        'email',
        'phone'
    ];

    public function __construct(
        private string $email,
        private UuidInterface $uuid,
        private string $name,
        private ?string $phone = null,
        private ?int $loginAttempts = null,
        private ?DateTimeImmutable $loginDate = null,
        private ?string $magicLink = null,
        private ?string $phoneCode = null,
        private string $passcodeRoute = 'email',
        private ?int $id = null
    ) {
        $this->emailIsValid($email);
        $this->passcodeRouteIsValid($passcodeRoute);
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'uuid' => $this->uuid
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        if ($this->emailIsValid($email)) {
            $this->email = $email;
        }
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    public function setIsLoggedIn(bool $isLoggedIn): void
    {
        $this->isLoggedIn = $isLoggedIn;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getLoginAttempts(): ?int
    {
        return $this->loginAttempts;
    }

    public function setLoginAttempts(int $loginAttempts): void
    {
        $this->loginAttempts = $loginAttempts;
    }

    public function getLoginDate(): ?DateTimeImmutable
    {
        return $this->loginDate;
    }

    public function getLoginDateString(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->loginDate?->format($format);
    }

    public function setLoginDate(string $loginDate): void
    {
        try {
            $this->loginDate = new DateTimeImmutable($loginDate);
        } catch (Exception) {
            throw new InvalidArgumentException('Could not create date instance');
        }
    }

    public function getMagicLink(): ?string
    {
        return $this->magicLink;
    }

    public function setMagicLink(?string $magicLink): void
    {
        $this->magicLink = $magicLink;
    }

    public function getPhoneCode(): ?string
    {
        return $this->phoneCode;
    }

    public function setPhoneCode(?string $phoneCode): void
    {
        $this->phoneCode = $phoneCode;
    }

    public function getPasscodeRoute(): string
    {
        return $this->passcodeRoute;
    }

    public function setPasscodeRoute(string $passcodeRoute): void
    {
        if ($this->passcodeRouteIsValid($passcodeRoute)) {
            $this->passcodeRoute = $passcodeRoute;
        }
    }

    private function emailIsValid(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email address');
        }
        return true;
    }
    private function passcodeRouteIsValid(string $passcodeRoute): bool
    {
        if (in_array($passcodeRoute, self::VALID_PASSCODE_ROUTES) === false) {
            throw new InvalidArgumentException('Passcode route must be one of ' . implode(', ', self::VALID_PASSCODE_ROUTES));
        }
        return true;
    }

    public static function createFromDataSet(DataSet $data): User
    {
        return new User(
            email: $data->string('email'),
            uuid: $data->uuidNull('uuid'),
            name: $data->string('name'),
            phone: $data->stringNull('phone'),
            loginAttempts: $data->intNull('login_attempts'),
            loginDate: $data->utcDateTimeNull('login_date'),
            magicLink: $data->stringNull('magic_link'),
            phoneCode: $data->stringNull('phone_code'),
            passcodeRoute: $data->string('passcode_route'),
            id: $data->int('id')
        );
    }
}
