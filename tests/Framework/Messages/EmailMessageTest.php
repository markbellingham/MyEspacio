<?php

declare(strict_types=1);

namespace Tests\Framework\Messages;

use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Messages\EmailMessage;
use PHPUnit\Framework\TestCase;

final class EmailMessageTest extends TestCase
{
    private TestableEmailMessage $msg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->msg = new TestableEmailMessage();
    }

    public function testEmailAddress()
    {
        $this->msg->setProtectedEmailAddress('name@example.tld');
        $this->assertEquals('name@example.tld', $this->msg->getEmailAddress());
    }

    public function testEmailAddressNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - email: ');
        $this->msg->setProtectedEmailAddress(null);
    }

    public function testEmailAddressInvalid()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - email: Mark Bellingham');
        $this->msg->setProtectedEmailAddress('Mark Bellingham');
    }

    public function testMessage()
    {
        $this->msg->setProtectedMessage('Test message greater than twenty characters');
        $this->assertEquals(
            'Test message greater than twenty characters',
            $this->msg->getMessage()
        );
    }

    public function testMessageNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - message: ');
        $this->msg->setProtectedMessage(null);
    }

    public function testMessageTooShort()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - message: Great Test Message!');
        $this->msg->setProtectedMessage('Great Test Message!');
    }

    public function testName()
    {
        $this->msg->setProtectedName('Mark Bellingham');
        $this->assertEquals('Mark Bellingham', $this->msg->getName());
    }

    public function testNameNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - name: ');
        $this->msg->setProtectedName(null);
    }

    public function testNameTooShort()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - name: Yo');
        $this->msg->setProtectedName('Yo');
    }

    public function testSubject()
    {
        $this->msg->setProtectedSubject('Test Subject');
        $this->assertEquals('Test Subject', $this->msg->getSubject());
    }

    public function testSubjectNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - subject: ');
        $this->msg->setProtectedSubject(null);
    }

    public function testSubjectTooShort()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - subject: Yo');
        $this->msg->setProtectedSubject('Yo');
    }
}
