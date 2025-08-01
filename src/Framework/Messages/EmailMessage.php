<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

use MyEspacio\Framework\Exceptions\InvalidEmailException;

class EmailMessage implements EmailMessageInterface
{
    private const int MINIMUM_MESSAGE_LENGTH = 20;
    private const int MINIMUM_NAME_LENGTH = 3;
    private const int MINIMUM_SUBJECT_LENGTH = 3;

    protected string $emailAddress;
    protected string $message;
    protected string $name;
    protected string $subject;
    protected string $error;

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @throws InvalidEmailException
     */
    protected function setEmailAddress(string $emailAddress): void
    {
        if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmailException::invalidMessage(['email' => $emailAddress]);
        }

        $this->emailAddress = $emailAddress;
    }

    /**
     * @throws InvalidEmailException
     */
    protected function setMessage(string $message): void
    {
        if (strlen($message) < self::MINIMUM_MESSAGE_LENGTH) {
            throw InvalidEmailException::invalidMessage(['message' => $message]);
        }

        $this->message = $message;
    }

    /**
     * @throws InvalidEmailException
     */
    protected function setName(string $name): void
    {
        if (strlen($name) < self::MINIMUM_NAME_LENGTH) {
            throw InvalidEmailException::invalidMessage(['name' => $name]);
        }
        $this->name = $name;
    }

    /**
     * @throws InvalidEmailException
     */
    protected function setSubject(string $subject): void
    {
        if (strlen($subject) < self::MINIMUM_SUBJECT_LENGTH) {
            throw InvalidEmailException::invalidMessage(['subject' => $subject]);
        }
        $this->subject = $subject;
    }
}
