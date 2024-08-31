<?php

declare(strict_types=1);

namespace MyEspacio\Common\Application;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;

interface CaptchaInterface
{
    public function validate(?int $iconId, ?string $encryptedIcon): bool;

    public function getIcons(int $quantity): CaptchaIconCollection;

    public function getEncryptedIcon(): string;

    public function getSelectedIcon(): CaptchaIcon;
}
