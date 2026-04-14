<?php

declare(strict_types=1);

namespace Tests\Php\Framework\Sanitization;

use MyEspacio\Framework\Sanitization\SafeHtmlSanitizer;
use MyEspacio\Framework\Sanitization\SafeHtmlSanitizerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SafeHtmlSanitizerTest extends TestCase
{
    private SafeHtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $factory = new SafeHtmlSanitizerFactory();

        $this->sanitizer = new SafeHtmlSanitizer(
            $factory->createContactMeSanitizer(),
            $factory->createCommentSanitizer()
        );
        parent::setUp();
    }

    #[DataProvider('harmfulContentRemovedDataProvider')]
    public function testHarmfulContentRemoved(
        string $input,
        string $needle,
    ): void {
        $contactMeOutput = $this->sanitizer->sanitizeContactMeMessage($input);
        $this->assertStringNotContainsString($needle, $contactMeOutput);

        $commentOutput = $this->sanitizer->sanitizeComment($input);
        $this->assertStringNotContainsString($needle, $commentOutput);
    }

    /** @return array<string, array<string, string>> */
    public static function harmfulContentRemovedDataProvider(): array
    {
        return [
            'script' => [
                'input' => '<script>alert(1)</script>',
                'needle' => '<script>',
            ],
            'style' => [
                'input' => '<p style="color:red;">Hello</p>',
                'needle' => 'style=',
            ],
            'img' => [
                'input' => '<img src="x.jpg" alt="alt text">',
                'needle' => '<img',
            ],
            'onclick' => [
                'input' => '<p onclick="alert(1)">Hello</p>',
                'needle' => 'onclick',
            ],
            'javascript_url' => [
                'input' => '<a href="javascript:alert(1)">Click</a>',
                'needle' => 'javascript:',
            ],
            'javascript_mixed_case' => [
                'input' => '<a href="JaVaScRiPt:alert(1)">Click</a>',
                'needle' => 'javascript:',
            ],
            'span_style' => [
                'input' => '<span style="color:red;">Hello</span>',
                'needle' => 'style=',
            ],
            'svg_script' => [
                'input' => '<svg><script>alert(1)</script></svg>',
                'needle' => '<script>',
            ],
            'broken_script' => [
                'input' => '<scr<script>ipt>alert(1)</scr</script>ipt>',
                'needle' => '<script>',
            ],
            'data_uri' => [
                'input' => '<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">Click</a>',
                'needle' => 'data:',
            ],
            'css_expression' => [
                'input' => '<p style="width: expression(alert(1));">Hello</p>',
                'needle' => 'expression',
            ],
        ];
    }

    #[DataProvider('allowedContentRemainsDataProvider')]
    public function testAllowedContentRemains(
        string $input,
        string $needle,
    ): void {
        $output = $this->sanitizer->sanitizeContactMeMessage($input);
        $this->assertStringContainsString($needle, $output);

        $output = $this->sanitizer->sanitizeComment($input);
        $this->assertStringContainsString($needle, $output);
    }

    /** @return array<string, array<string, string>> */
    public static function allowedContentRemainsDataProvider(): array
    {
        return [
            'paragraph' => [
                'input' => '<p>Hello world</p>',
                'needle' => '<p>',
            ],
            'line_break' => [
                'input' => 'Hello<br>World',
                'needle' => '<br',
            ],
            'strong' => [
                'input' => '<strong>Bold</strong>',
                'needle' => '<strong>',
            ],
            'emphasis' => [
                'input' => '<em>Italic</em>',
                'needle' => '<em>',
            ],
            'nested_formatting' => [
                'input' => '<p>Hello <strong>world</strong></p>',
                'needle' => '<strong>',
            ],
        ];
    }

    #[DataProvider('policyDifferenceDataProvider')]
    public function testPolicyDifferences(
        string $input,
        string $contactNeedle,
        string $commentNeedle,
    ): void {
        $contactOutput = $this->sanitizer->sanitizeContactMeMessage($input);
        $this->assertStringNotContainsString($contactNeedle, $contactOutput);

        $commentOutput = $this->sanitizer->sanitizeComment($input);
        $this->assertStringContainsString($commentNeedle, $commentOutput);
    }

    /** @return array<string, array{input: string, contactNeedle: string, commentNeedle: string}> */
    public static function policyDifferenceDataProvider(): array
    {
        return [
            'link_allowed_only_in_comments' => [
                'input' => '<a href="https://example.com">Link</a>',
                'contactNeedle' => '<a',
                'commentNeedle' => '<a',
            ],
        ];
    }
}
