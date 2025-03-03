<?php

declare(strict_types=1);

namespace MyEspacio\User\Application;

use Exception;
use MyEspacio\Framework\Messages\EmailMessage;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\User\Domain\User;

final class LoginEmailMessage extends EmailMessage implements LoginEmailMessageInterface
{
    private const string EMAIL_SUBJECT = 'Your Activation Code';

    public function __construct(
        private readonly TemplateRendererFactoryInterface $templateRendererFactory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function assemble(User $user): void
    {
        $this->setName($user->getName());
        $this->setEmailAddress($user->getEmail());
        $this->setSubject(self::EMAIL_SUBJECT);
        $this->setMessage($this->markup($user));
    }

    private function markup(User $user): string
    {
        $templateRenderer = $this->templateRendererFactory->create();
        return $templateRenderer->render('user/LoginEmail.html.twig', [
            'user' => $user,
            'domain_root' => CONFIG['domain_root'],
            'website_owner' => CONFIG['contact']['name'],
        ]);
    }
}
