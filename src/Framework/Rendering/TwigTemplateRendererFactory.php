<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Rendering;

use MyEspacio\Common\Application\Captcha;
use MyEspacio\Framework\Csrf\StoredTokenReader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

final class TwigTemplateRendererFactory
{
    private ?TemplateRenderer $templateRenderer = null;
    private ?Translator $translator = null;

    public function __construct(
        private readonly Captcha $captcha,
        private readonly StoredTokenReader $storedTokenReader,
        private readonly TemplateDirectory $templateDirectory,
        private readonly TranslatorFactory $translatorFactory
    ) {
    }

    public function create(string $locale = 'en'): TwigTemplateRenderer
    {
        $loader = new FilesystemLoader($this->templateDirectory->toString());
        $twigEnvironment = new Environment($loader, [
            'debug' => true,
            'auto_reload' => true
        ]);

        $twigEnvironment->addFunction(
            new TwigFunction('get_token', function (string $key): string {
                $token = $this->storedTokenReader->read($key);
                return $token->toString();
            })
        );

        $twigEnvironment->addFunction(
            new TwigFunction('captcha_icons', function (string $section, int $qty = 5): string {
                return $this->createCaptchaIcons($section, $qty);
            })
        );

        $this->translator = $this->translatorFactory->createTranslator($locale);
        $twigEnvironment->addFunction(
            new TwigFunction('trans', function (string $message, array $parameters = []): string {
                return $this->translator->trans($message, $parameters);
            })
        );

        $this->templateRenderer = new TwigTemplateRenderer($twigEnvironment);
        return $this->templateRenderer;
    }

    private function createCaptchaIcons(string $section, int $qty = 5): string
    {
        $icons = $this->captcha->getIcons($qty);
        return $this->templateRenderer->render('common/CaptchaIcons.html.twig', [
            'captcha1' => $this->captcha->getSelectedIcon(),
            'captcha2' => $this->captcha->getEncryptedIcon(),
            'icons' => $icons,
            'section' => $section
        ]);
    }
}
