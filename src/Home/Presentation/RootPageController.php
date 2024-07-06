<?php

declare(strict_types=1);

namespace MyEspacio\Home\Presentation;

use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RootPageController
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly TwigTemplateRendererFactory $templateRendererFactory,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function show(Request $request, array $vars): Response
    {
        $user = $this->session->get('user') ?? $this->userRepository->getAnonymousUser();

        $params = [
            'title' => CONFIG['contact']['name'],
            'user' => $user,
        ];

        $templateRenderer = $this->templateRendererFactory->create('en');
        $content = $templateRenderer->render('Layout.html.twig', $params);
        return new Response($content);
    }
}
