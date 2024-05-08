<?php

declare(strict_types=1);

namespace Tests\User\Domain;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\User\Domain\LoginEmailMessage;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\TestCase;

final class LoginEmailMessageTest extends TestCase
{
    public function testAssemble()
    {
        $user = new User(
            email: 'website@mexample.com',
            uuid: 'bfb1fd80-a41a-4b25-bcf9-3a4a3e108f92',
            name: 'Mark Bellingham',
            id: 1
        );

        $templateRenderer = $this->createMock(TemplateRenderer::class);
        $templateRenderer->expects($this->once())
            ->method('render')
            ->willReturn('<html lang=""><body>Login email content</body></html>');

        $loginEmailMessage = new LoginEmailMessage($templateRenderer);
        $loginEmailMessage->assemble($user);

        $this->assertEquals('Mark Bellingham', $loginEmailMessage->getName());
        $this->assertEquals('website@mexample.com', $loginEmailMessage->getEmailAddress());
        $this->assertEquals('Your Activation Code', $loginEmailMessage->getSubject());
        $this->assertEquals(
            $loginEmailMessage->getMessage(),
            '<html lang=""><body>Login email content</body></html>'
        );
    }

    public function testAssembleWithException()
    {
        $user = new User(
            email: 'website@mexample.com',
            uuid: 'bfb1fd80-a41a-4b25-bcf9-3a4a3e108f92',
            name: '',
            id: 1
        );

        $templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - name:');
        $loginEmailMessage = new LoginEmailMessage($templateRenderer);
        $loginEmailMessage->assemble($user);
    }
}
