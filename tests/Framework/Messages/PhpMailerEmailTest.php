<?php

declare(strict_types=1);

namespace Tests\Framework\Messages;

use MyEspacio\Framework\Messages\PhpMailerEmail;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

class PhpMailerEmailTest extends TestCase
{
    public function testSendEmail(): void
    {
        $phpMailer = $this->createMock(PHPMailer::class);
        $phpMailer->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $emailMessage = new TestableEmailMessage();
        $emailMessage->setProtectedEmailAddress('test@example.tld');
        $emailMessage->setProtectedMessage('Test message greater than twenty characters');
        $emailMessage->setProtectedName('Mark Bellingham');
        $emailMessage->setProtectedSubject('A Great Test Subject');
        try {
            $email = new PhpMailerEmail($phpMailer);
            $result = $email->send($emailMessage);
            $this->assertTrue($result);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendEmailFail(): void
    {
        $phpMailer = $this->createMock(PHPMailer::class);
        $phpMailer->expects($this->once())
            ->method('send')
            ->willReturn(false);

        $emailMessage = new TestableEmailMessage();
        $emailMessage->setProtectedEmailAddress('test@example.tld');
        $emailMessage->setProtectedMessage('Test message greater than twenty characters');
        $emailMessage->setProtectedName('Mark Bellingham');
        $emailMessage->setProtectedSubject('A Great Test Subject');
        try {
            $email = new PhpMailerEmail($phpMailer);
            $result = $email->send($emailMessage);
            $this->assertFalse($result);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
