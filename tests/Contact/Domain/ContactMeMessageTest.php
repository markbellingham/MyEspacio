<?php

declare(strict_types=1);

namespace Tests\Contact\Domain;

use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use PHPUnit\Framework\TestCase;

final class ContactMeMessageTest extends TestCase
{
    public function testContactMeMessage()
    {
        $message = new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: 1,
            description: ''
        );

        $this->assertInstanceOf(ContactMeMessage::class, $message);
    }

    public function testCaptchaIdNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - captchaIconId: ');

        $message = new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: null,
            description: ''
        );
    }

    public function testDescriptionNull()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - captchaIconId: 1');

        $message = new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: 1,
            description: null
        );
    }

    public function testDescriptionPopulated()
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - captchaIconId: 1, description: An invalid description');

        $message = new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: 1,
            description: 'An invalid description'
        );
    }
}
