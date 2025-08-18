<?php

declare(strict_types=1);

namespace Tests\User\Application;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\User\Application\LoginEmailMessage;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class LoginEmailMessageTest extends TestCase
{
    #[DataProvider('assembleDataProvider')]
    public function testAssemble(
        string $emailBodyHtml,
        User $user,
        string $userName,
        string $userEmail,
        string $emailSubject,
    ): void {
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $templateRenderer = $this->createMock(TemplateRenderer::class);
        $templateRendererFactory->method('create')->willReturn($templateRenderer);
        $templateRenderer->expects($this->once())
            ->method('render')
            ->willReturn($emailBodyHtml);

        $loginEmailMessage = new LoginEmailMessage($templateRendererFactory);
        $loginEmailMessage->assemble($user);

        $this->assertEquals($userName, $loginEmailMessage->getName());
        $this->assertEquals($userEmail, $loginEmailMessage->getEmailAddress());
        $this->assertEquals($emailSubject, $loginEmailMessage->getSubject());
        $this->assertEquals(
            $loginEmailMessage->getMessage(),
            $emailBodyHtml
        );
    }

    /** @return array<string, array<string, mixed>> */
    public static function assembleDataProvider(): array
    {
        return [
            'test_1' => [
                'emailBodyHtml' => '<html lang=""><body>Login email content</body></html>',
                'user' => new User(
                    email: 'website@mexample.com',
                    uuid: Uuid::fromString('bfb1fd80-a41a-4b25-bcf9-3a4a3e108f92'),
                    name: 'Mark Bellingham',
                    id: 1
                ),
                'userName' => 'Mark Bellingham',
                'userEmail' => 'website@mexample.com',
                'emailSubject' => 'Your Activation Code',
            ],
            'test_2' => [
                'emailBodyHtml' => '<html lang=""><body>Welcome to our platform! Please verify your account.</body></html>',
                'user' => new User(
                    email: 'sarah.johnson@example.org',
                    uuid: Uuid::fromString('d3c7f291-8e45-4a73-9b12-7f8e6d4c2a91'),
                    name: 'Sarah Johnson',
                    id: 2
                ),
                'userName' => 'Sarah Johnson',
                'userEmail' => 'sarah.johnson@example.org',
                'emailSubject' => 'Your Activation Code',
            ],
        ];
    }

    public function testAssembleWithException(): void
    {
        $user = new User(
            email: 'website@mexample.com',
            uuid: Uuid::fromString('bfb1fd80-a41a-4b25-bcf9-3a4a3e108f92'),
            name: '',
            id: 1
        );

        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $templateRenderer = $this->createMock(TemplateRenderer::class);
        $templateRendererFactory->method('create')->willReturn($templateRenderer);
        $templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - name:');
        $loginEmailMessage = new LoginEmailMessage($templateRendererFactory);
        $loginEmailMessage->assemble($user);
    }
}
