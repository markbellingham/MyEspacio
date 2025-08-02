<?php

declare(strict_types=1);

namespace Tests\Contact\Domain;

use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Messages\EmailMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContactMeMessageTest extends TestCase
{
    #[DataProvider('contactMessageDataProvider')]
    public function testContactMeMessage(
        string $emailAddress,
        string $name,
        string $subject,
        string $message,
        int $captchaIconId,
        string $description,
    ): void {
        $contactMessage = new ContactMeMessage(
            $emailAddress,
            $name,
            $subject,
            $message,
            $captchaIconId,
            $description,
        );

        $this->assertSame($emailAddress, $contactMessage->getEmailAddress());
        $this->assertSame($name, $contactMessage->getName());
        $this->assertSame($subject, $contactMessage->getSubject());
        $this->assertSame($message, $contactMessage->getMessage());

        $this->assertInstanceOf(EmailMessage::class, $contactMessage);
    }

    /** @return array<string, array<string, mixed>> */
    public static function contactMessageDataProvider(): array
    {
        return [
            'test_1' => [
                'emailAddress' => 'mail@example.tld',
                'name' => 'Anonymous',
                'subject' => 'A Test Subject',
                'message' => 'A test message with lots of characters',
                'captchaIconId' => 1,
                'description' => '',
            ],
            'test_2' => [
                'emailAddress' => 'joe@bloggs.com',
                'name' => 'Joe Bloggs',
                'subject' => 'Testing testing',
                'message' => 'This is a test message',
                'captchaIconId' => 2,
                'description' => '',
            ]
        ];
    }

    #[DataProvider('exceptionsDataProvider')]
    public function testExceptions(
        string $exceptionMessage,
        string $emailAddress,
        string $name,
        string $subject,
        string $message,
        ?int $captchaIconId,
        ?string $description,
    ): void {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new ContactMeMessage(
            $emailAddress,
            $name,
            $subject,
            $message,
            $captchaIconId,
            $description,
        );
    }

    /** @return array<string, array<string, mixed>> */
    public static function exceptionsDataProvider(): array
    {
        return [
            'captcha_id_null' => [
                'exceptionMessage' => 'Invalid Message - emailAddress: mail@example.tld, message: A test message with lots of characters, name: Anonymous, subject: A Test Subject, captchaIconId: null, description:',
                'emailAddress' => 'mail@example.tld',
                'name' => 'Anonymous',
                'subject' => 'A Test Subject',
                'message' => 'A test message with lots of characters',
                'captchaIconId' => null,
                'description' => '',
            ],
            'description_null' => [
                'exceptionMessage' => 'Invalid Message - emailAddress: joe@bloggs.com, message: A test message, name: Joe Bloggs, subject: Testing testing, captchaIconId: 1, description: null',
                'emailAddress' => 'joe@bloggs.com',
                'name' => 'Joe Bloggs',
                'subject' => 'Testing testing',
                'message' => 'A test message',
                'captchaIconId' => 1,
                'description' => null,
            ],
            'description_populated' => [
                'exceptionMessage' => 'Invalid Message - emailAddress: TestName@gmail.com, message: A boring test message, name: Mr Test Name, subject: A boring test subject, captchaIconId: 2, description: An invalid description',
                'emailAddress' => 'TestName@gmail.com',
                'name' => 'Mr Test Name',
                'subject' => 'A boring test subject',
                'message' => 'A boring test message',
                'captchaIconId' => 2,
                'description' => 'An invalid description'
            ],
        ];
    }
}
