<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

final class PhpMailerEmail implements EmailInterface
{
    private const SMTP_SERVER = 'smtp.gmail.com';
    private const SMTP_PORT = 465;

    /**
     * @throws Exception
     */
    public function __construct(private readonly PHPMailer $phpMailer)
    {
        $this->phpMailer->isSMTP();
        $this->phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
        $this->phpMailer->Host = self::SMTP_SERVER;
        $this->phpMailer->Port = self::SMTP_PORT;
        $this->phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->phpMailer->SMTPAuth = true;

        $this->phpMailer->Username = CONFIG['google']['email'];
        $this->phpMailer->Password = CONFIG['google']['apppassword'];
        $this->phpMailer->setFrom(CONFIG['contact']['email'], CONFIG['contact']['name']);
        $this->phpMailer->addReplyTo(CONFIG['contact']['email'], CONFIG['contact']['name']);
    }

    /**
     * @throws Exception
     */
    public function send(EmailMessageInterface $emailMessage): bool
    {
        $this->phpMailer->addAddress($emailMessage->getEmailAddress(), $emailMessage->getName());
        $this->phpMailer->Subject = $emailMessage->getSubject();
        $this->phpMailer->msgHTML($emailMessage->getMessage());
        return $this->phpMailer->send();
    }
}
