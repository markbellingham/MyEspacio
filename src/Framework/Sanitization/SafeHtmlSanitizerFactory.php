<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Sanitization;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class SafeHtmlSanitizerFactory
{
    public function createContactMeSanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->blockElement('a')
            ->blockElement('img')
            ->blockElement('iframe')
            ->dropAttribute('*', 'style');

        return new HtmlSanitizer($config);
    }

    public function createCommentSanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowElement('a', ['href', 'title'])
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('p')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowRelativeLinks()
            ->blockElement('img')
            ->blockElement('iframe')
            ->dropAttribute('*', 'style');

        return new HtmlSanitizer($config);
    }
}
