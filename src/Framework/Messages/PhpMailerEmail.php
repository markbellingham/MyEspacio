<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Messages;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

final class PhpMailerEmail implements Email
{
    public array $errors;

    private const SMTP_SERVER = 'smtp.gmail.com';
    private const SMTP_PORT = 465;
    //You can change 'Sent Mail' to any other folder or tag
    private const IMAP_PATH = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

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
    public function send(EmailMessage $emailMessage): bool
    {
        $this->phpMailer->addAddress($emailMessage->getEmailAddress(), $emailMessage->getName());
        $this->phpMailer->Subject = $emailMessage->getSubject();
        $this->phpMailer->msgHTML($emailMessage->getMessage());
        return $this->phpMailer->send();
    }

    //Section 2: IMAP
    //IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
    //Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
    //You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
    //be useful if you are trying to get this working on a non-Gmail IMAP server.
    private function saveImap(): bool
    {
        //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
        $imapStream = imap_open(self::IMAP_PATH, $this->phpMailer->Username, $this->phpMailer->Password);

        $result = imap_append($imapStream, self::IMAP_PATH, $this->phpMailer->getSentMIMEMessage());
        imap_close($imapStream);

        return $result;
    }
}
