<?php

declare(strict_types=1);

namespace MyEspacio\Home\Presentation;

use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RootPageController
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly TemplateRenderer $templateRenderer,
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

        $content = $this->templateRenderer->render('Layout.html.twig', $params);
        return new Response($content);
    }
}
