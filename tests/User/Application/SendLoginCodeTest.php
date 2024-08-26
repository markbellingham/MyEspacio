<?php

declare(strict_types=1);

namespace Tests\User\Application;

use Exception;
use MyEspacio\Framework\Messages\EmailInterface;
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Domain\LoginEmailMessage;
use MyEspacio\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SendLoginCodeTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(
            email: 'mail@example.com',
            uuid:'e762349c-a60e-4428-b781-a076e161f1e3',
            name: 'Mark Bellingham',
            passcodeRoute: 'email'
        );
    }

    public function testGenerateCode(): void
    {
        $user = new User(
            email: 'mail@example.com',
            uuid:'e762349c-a60e-4428-b781-a076e161f1e3',
            name: 'Mark Bellingham'
        );

        /** @var LoginEmailMessage|MockObject $loginEmailMessage */
        $loginEmailMessage = $this->createMock(LoginEmailMessage::class);
        $emailInterface = $this->createMock(EmailInterface::class);

        $sendLoginCode = new SendLoginCode($loginEmailMessage, $emailInterface);
        $user = $sendLoginCode->generateCode($user);
        $this->assertEquals(40, strlen($user->getMagicLink()));
        $this->assertEquals(6, strlen($user->getPhoneCode()));
    }

    public function testSendToUser(): void
    {
        $loginEmailMessage = $this->createMock(LoginEmailMessage::class);
        $emailInterface = $this->createMock(EmailInterface::class);
        $emailInterface->expects($this->once())
            ->method('send')
            ->with($loginEmailMessage)
            ->willReturn(true);

        $sendLoginCode = new SendLoginCode($loginEmailMessage, $emailInterface);
        $result = $sendLoginCode->sendToUser($this->user);
        $this->assertTrue($result);
    }

    public function testSendToUserFail(): void
    {
        $loginEmailMessage = $this->createMock(LoginEmailMessage::class);
        $emailInterface = $this->createMock(EmailInterface::class);
        $emailInterface->expects($this->once())
            ->method('send')
            ->with($loginEmailMessage)
            ->willReturn(false);

        $sendLoginCode = new SendLoginCode($loginEmailMessage, $emailInterface);
        $result = $sendLoginCode->sendToUser($this->user);
        $this->assertFalse($result);
    }

    public function testSendToUserEmailException(): void
    {
        $loginEmailMessage = $this->createMock(LoginEmailMessage::class);
        $emailInterface = $this->createMock(EmailInterface::class);
        $emailInterface->method('send')
            ->willThrowException(new Exception('Error'));

        $sendLoginCode = new SendLoginCode($loginEmailMessage, $emailInterface);
        $result = $sendLoginCode->sendToUser($this->user);
        $this->assertFalse($result);
    }

    public function testSendToUserByText(): void
    {
        $this->user->setPasscodeRoute('phone');
        $loginEmailMessage = $this->createMock(LoginEmailMessage::class);
        $emailInterface = $this->createMock(EmailInterface::class);

        $sendLoginCode = new SendLoginCode($loginEmailMessage, $emailInterface);
        $result = $sendLoginCode->sendToUser($this->user);
        $this->assertFalse($result);
    }
}
