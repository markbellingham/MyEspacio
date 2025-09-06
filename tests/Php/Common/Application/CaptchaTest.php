<?php

declare(strict_types=1);

namespace Tests\Php\Php\Common\Application;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Entity\CaptchaIcon;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CaptchaTest extends TestCase
{
    private Captcha $captcha;

    protected function setUp(): void
    {
        parent::setUp();
        $iconRepository = $this->createMock(IconRepositoryInterface::class);
        $this->captcha = new Captcha($iconRepository);

        $iconRepository->expects($this->once())
            ->method('getIcons')
            ->willReturn(new CaptchaIconCollection(
                [
                    [
                        'icon_id' => '1',
                        'icon' => '<i class="bi bi-phone-vibrate"></i>',
                        'name' => 'Mobile'
                    ]
                ]
            ));
    }

    public function testValidate(): void
    {
        $this->captcha->getIcons(5);

        $isValid = $this->captcha->validate(
            $this->captcha->getSelectedIcon()->getIconId(),
            $this->captcha->getEncryptedIcon()
        );

        $this->assertTrue($isValid);
    }

    public function testValidateWithInvalidData(): void
    {
        $this->captcha->getIcons(5);

        $isValid = $this->captcha->validate(null, null);
        $this->assertFalse($isValid);

        $isValidInvalidIconId = $this->captcha->validate(100, $this->captcha->getEncryptedIcon());
        $this->assertFalse($isValidInvalidIconId);

        $isValidInvalidEncryptedIcon = $this->captcha->validate($this->captcha->getSelectedIcon()->getIconId(), 'invalid_encrypted_icon');
        $this->assertFalse($isValidInvalidEncryptedIcon);
    }

    public function testGetIcons(): void
    {
        $icons = $this->captcha->getIcons(5);
        $this->assertInstanceOf(CaptchaIconCollection::class, $icons);
    }

    public function testGetSelectedIcon(): void
    {
        $this->captcha->getIcons(5);
        $this->assertInstanceOf(CaptchaIcon::class, $this->captcha->getSelectedIcon());
    }

    public function testGetEncryptedIcon(): void
    {
        $this->captcha->getIcons(5);
        $this->assertEquals(60, strlen($this->captcha->getEncryptedIcon()));
    }

    public function testJsonSerialize(): void
    {
        $this->captcha->getIcons(5);
        $array = $this->captcha->jsonSerialize();
        $this->assertArrayHasKey('encryptedIcon', $array);
        $this->assertArrayHasKey('selectedIcon', $array);
        $this->assertInstanceOf(CaptchaIcon::class, $array['selectedIcon']);
    }
}
