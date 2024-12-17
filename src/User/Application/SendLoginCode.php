<?php

declare(strict_types=1);

namespace MyEspacio\User\Application;

use Exception;
use MyEspacio\Framework\Messages\EmailInterface;
use MyEspacio\User\Domain\User;

final class SendLoginCode implements SendLoginCodeInterface
{
    private const int MAGIC_LINK_LENGTH = 20;
    private const int PHONE_CODE_LENGTH = 3;

    public function __construct(
        private readonly LoginEmailMessageInterface $loginEmailMessage,
        private readonly EmailInterface $email
    ) {
    }

    /**
     * @throws Exception
     */
    public function generateCode(User $user): User
    {
        $user->setMagicLink(bin2hex(random_bytes(self::MAGIC_LINK_LENGTH)));
        $user->setPhoneCode(bin2hex(random_bytes(self::PHONE_CODE_LENGTH)));
        return $user;
    }

    public function sendTo(User $user): bool
    {
        if ($user->getPasscodeRoute() == 'email') {
            return $this->sendByEmail($user);
        } else {
            return $this->sendByText($user);
        }
    }

    private function sendByEmail(User $user): bool
    {
        try {
            $this->loginEmailMessage->assemble($user);
            return $this->email->send($this->loginEmailMessage);
        } catch (Exception) {
            return false;
        }
    }

    private function sendByText(User $user): bool
    {
        return false;
    }
}
