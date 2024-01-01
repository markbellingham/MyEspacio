<?php

declare(strict_types=1);

namespace Tests\Common\Application;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Domain\CaptchaIcon;
use MyEspacio\Common\Domain\CaptchaIconCollection;
use MyEspacio\Common\Infrastructure\IconsRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;

final class CaptchaTest extends TestCase
{
    private Captcha $captcha;

    protected function setUp(): void
    {
        parent::setUp();
        $iconsRepository = $this->createMock(IconsRepository::class);
        $session = $this->createMock(Session::class);
        $this->captcha = new Captcha($iconsRepository, $session);

        $iconsRepository->expects($this->once())
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

    public function testValidate()
    {
        $this->captcha->getIcons(5);

        $isValid = $this->captcha->validate(
            $this->captcha->getSelectedIcon()->getIconId(),
            $this->captcha->getEncryptedIcon()
        );

        $this->assertTrue($isValid);
    }

    public function testValidateWithInvalidData()
    {
        $this->captcha->getIcons(5);

        $isValid = $this->captcha->validate(null, null);
        $this->assertFalse($isValid);

        $isValidInvalidIconId = $this->captcha->validate(100, $this->captcha->getEncryptedIcon());
        $this->assertFalse($isValidInvalidIconId);

        $isValidInvalidEncryptedIcon = $this->captcha->validate($this->captcha->getSelectedIcon()->getIconId(), 'invalid_encrypted_icon');
        $this->assertFalse($isValidInvalidEncryptedIcon);
    }

    public function testGetIcons()
    {
        $icons = $this->captcha->getIcons(5);
        $this->assertInstanceOf(CaptchaIconCollection::class, $icons);
    }

    public function testGetSelectedIcon()
    {
        $this->captcha->getIcons(5);
        $this->assertInstanceOf(CaptchaIcon::class, $this->captcha->getSelectedIcon());
    }

    public function testGetEncryptedIcon()
    {
        $this->captcha->getIcons(5);
        $this->assertEquals(60, strlen($this->captcha->getEncryptedIcon()));
    }

    public function testJsonSerialize()
    {
        $this->captcha->getIcons(5);
        $array = $this->captcha->jsonSerialize();
        $this->assertArrayHasKey('encryptedIcon', $array);
        $this->assertArrayHasKey('selectedIcon', $array);
        $this->assertInstanceOf(CaptchaIcon::class, $array['selectedIcon']);
    }
}
