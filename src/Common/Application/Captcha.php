<?php

declare(strict_types=1);

namespace MyEspacio\Common\Application;

use JsonSerializable;
use MyEspacio\Common\Domain\CaptchaIcon;
use MyEspacio\Common\Domain\CaptchaIconCollection;
use MyEspacio\Common\Infrastructure\IconsRepository;
use MyEspacio\Framework\Config\Settings;
use Symfony\Component\HttpFoundation\Session\Session;

final class Captcha implements JsonSerializable
{
    private CaptchaIconCollection $icons;
    private string $encryptedIcon;
    private CaptchaIcon $selectedIcon;

    public function __construct(
        private readonly IconsRepository $iconsRepository
    ) {
    }

    public function validate(?int $iconId, ?string $encryptedIcon): bool
    {
        if ($iconId == null || $encryptedIcon == null) {
            return false;
        }
        return password_verify(date('dmY') . Settings::getServerSecret() . $iconId, $encryptedIcon);
    }

    private function chooseSelectedIcon(): void
    {
        $icons = $this->icons->toArray();
        $index = array_rand($icons);
        $this->selectedIcon = $this->icons[$index];
    }

    private function encryptIcon(): void
    {
        $this->encryptedIcon = password_hash(
            date('dmY') . Settings::getServerSecret() . $this->selectedIcon->getIconId(),
            PASSWORD_DEFAULT
        );
    }

    public function getIcons(int $qty): CaptchaIconCollection
    {
        $this->icons = $this->iconsRepository->getIcons($qty);
        $this->chooseSelectedIcon();
        $this->encryptIcon();
        return $this->icons;
    }

    public function getEncryptedIcon(): string
    {
        return $this->encryptedIcon;
    }

    public function getSelectedIcon(): CaptchaIcon
    {
        return $this->selectedIcon;
    }

    public function jsonSerialize(): array
    {
        return [
            'encryptedIcon' => $this->encryptedIcon,
            'selectedIcon' => $this->selectedIcon
        ];
    }
}
