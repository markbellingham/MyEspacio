<?php

declare(strict_types=1);

namespace MyEspacio\Home\Presentation;

use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\User\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class RootPageController
{
    public function __construct(
        private SessionInterface $session,
        private TemplateRendererFactoryInterface $templateRendererFactory,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param Request $request
     * @param array<string, mixed> $vars
     * @return Response
     */
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
