<?php

declare(strict_types=1);

namespace Tests\Contact\Domain;

use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Messages\EmailMessage;
use PHPUnit\Framework\TestCase;

final class ContactMeMessageTest extends TestCase
{
    public function testContactMeMessage(): void
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
        $this->assertInstanceOf(EmailMessage::class, $message);
    }

    public function testCaptchaIdNull(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - emailAddress: mail@example.tld, message: A test message with lots of characters, name: Anonymous, subject: A Test Subject, captchaIconId: null, description:');

        new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: null,
            description: ''
        );
    }

    public function testDescriptionNull(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - emailAddress: mail@example.tld, message: A test message with lots of characters, name: Anonymous, subject: A Test Subject, captchaIconId: 1, description: null');

        new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: 1,
            description: null
        );
    }

    public function testDescriptionPopulated(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid Message - emailAddress: mail@example.tld, message: A test message with lots of characters, name: Anonymous, subject: A Test Subject, captchaIconId: 1, description: An invalid description');

        new ContactMeMessage(
            emailAddress: 'mail@example.tld',
            name: 'Anonymous',
            subject: 'A Test Subject',
            message: 'A test message with lots of characters',
            captchaIconId: 1,
            description: 'An invalid description'
        );
    }
}
