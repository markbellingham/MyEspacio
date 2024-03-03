<?php

declare(strict_types=1);

namespace MyEspacio\User\Domain;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * @throws InvalidArgumentException
 */
final class User implements JsonSerializable
{
    private bool $isLoggedIn = false;

    private const VALID_PASSCODE_ROUTES = [
        'email',
        'phone'
    ];

    public function __construct(
        private string $email,
        private string $uuid,
        private string $name = 'Anonymous',
        private ?string $phone = null,
        private ?int $login_attempts = null,
        private ?DateTimeImmutable $login_date = null,
        private ?string $magic_link = null,
        private ?string $phone_code = null,
        private string $passcodeRoute = 'email',
        private ?int $id = null
    ) {
        $this->uuidIsValid($uuid);
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): void
    {
        if ($this->uuidIsValid($uuid)) {
            $this->uuid = $uuid;
        }
    }

    public function getLoginAttempts(): ?int
    {
        return $this->login_attempts;
    }

    public function setLoginAttempts(int $login_attempts): void
    {
        $this->login_attempts = $login_attempts;
    }

    public function getLoginDate(): ?DateTimeImmutable
    {
        return $this->login_date;
    }

    public function getLoginDateString(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->login_date?->format($format);
    }

    public function setLoginDate(string $login_date): void
    {
        try {
            $this->login_date = new DateTimeImmutable($login_date);
        } catch (Exception) {
            throw new InvalidArgumentException('Could not create date instance');
        }
    }

    public function getMagicLink(): ?string
    {
        return $this->magic_link;
    }

    public function setMagicLink(?string $magic_link): void
    {
        $this->magic_link = $magic_link;
    }

    public function getPhoneCode(): ?string
    {
        return $this->phone_code;
    }

    public function setPhoneCode(?string $phone_code): void
    {
        $this->phone_code = $phone_code;
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

    private function uuidIsValid(?string $uuid): bool
    {
        if ($uuid !== null && Uuid::isValid($uuid) === false) {
            throw new InvalidArgumentException('Invalid UUID');
        }
        return true;
    }
    private function emailIsValid(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_BOOL)) {
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
}
