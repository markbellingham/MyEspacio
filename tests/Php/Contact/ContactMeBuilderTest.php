<?php

declare(strict_types=1);

namespace Tests\Php\Contact;

use MyEspacio\Contact\Application\ContactMeBuilder;
use MyEspacio\Contact\Domain\ContactMeMessage;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Exceptions\InvalidEmailException;
use MyEspacio\Framework\Rendering\TemplateRenderer;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\Framework\Sanitization\SafeHtmlSanitizerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContactMeBuilderTest extends TestCase
{
    /** @param array<string, string> $sanitisedData */
    #[DataProvider('buildDataProvider')]
    public function testBuild(
        DataSet $data,
        string $rawEmailMessage,
        string $cleanedEmailMessage,
        array $sanitisedData,
        string $renderedTemplate,
        ContactMeMessage $expectedResult,
    ): void {
        $sanitizer = $this->createMock(SafeHtmlSanitizerInterface::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);
        $templateRenderer = $this->createMock(TemplateRenderer::class);

        $sanitizer->expects($this->once())
            ->method('sanitizeContactMeMessage')
            ->with($rawEmailMessage)
            ->willReturn($cleanedEmailMessage);
        $templateRenderer->expects($this->once())
            ->method('render')
            ->with('contact/ContactMeEmail.html.twig', $sanitisedData)
            ->willReturn($renderedTemplate);
        $templateRendererFactory->expects($this->once())
            ->method('create')
            ->willReturn($templateRenderer);

        $builder = new ContactMeBuilder(
            $sanitizer,
            $templateRendererFactory
        );

        $actualResult = $builder->build($data);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @return array<string, array<string, mixed>> */
    public static function buildDataProvider(): array
    {
        return [
            'test_normal' => [
                'data' => new DataSet([
                    'emailAddress' => 'mail@example.tld',
                    'name' => 'John Doe',
                    'subject' => 'Test Subject',
                    'message' => 'Even Longer Test Message',
                    'captcha1' => 1,
                    'description' => '',
                ]),
                'rawEmailMessage' => 'Even Longer Test Message',
                'cleanedEmailMessage' => 'Even Longer Test Message',
                'sanitisedData' => [
                    'emailAddress' => 'mail@example.tld',
                    'name' => 'John Doe',
                    'subject' => 'Test Subject',
                    'message' => 'Even Longer Test Message',
                ],
                'renderedTemplate' => '<div>Even Longer Test Message</div>',
                'expectedResult' => new ContactMeMessage(
                    emailAddress: 'mail@example.tld',
                    name: 'John Doe',
                    subject: 'Test Subject',
                    message: '<div>Even Longer Test Message</div>',
                    captchaIconId: 1,
                    description: '',
                ),
            ],
            'with_script' => [
                'data' => new DataSet([
                    'emailAddress' => 'mail@example.tld',
                    'name' => 'John Doe',
                    'subject' => 'Test Subject',
                    'message' => 'Even Longer Test Message<script>alert(1)</script>',
                    'captcha1' => 1,
                    'description' => '',
                ]),
                'rawEmailMessage' => 'Even Longer Test Message<script>alert(1)</script>',
                'cleanedEmailMessage' => 'Even Longer Test Message',
                'sanitisedData' => [
                    'emailAddress' => 'mail@example.tld',
                    'name' => 'John Doe',
                    'subject' => 'Test Subject',
                    'message' => 'Even Longer Test Message',
                ],
                'renderedTemplate' => '<div>Even Longer Test Message</div>',
                'expectedResult' => new ContactMeMessage(
                    emailAddress: 'mail@example.tld',
                    name: 'John Doe',
                    subject: 'Test Subject',
                    message: '<div>Even Longer Test Message</div>',
                    captchaIconId: 1,
                    description: '',
                ),
            ]
        ];
    }

    #[DataProvider('builderEmptyMessageDataProvider')]
    public function testBuildEmptyMessage(
        string $rawMessage,
        string $cleanedMessage,
    ): void {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Message cannot be empty.');

        $sanitizer = $this->createMock(SafeHtmlSanitizerInterface::class);
        $templateRendererFactory = $this->createMock(TemplateRendererFactoryInterface::class);

        $data = new DataSet([
            'emailAddress' => 'mail@example.tld',
            'name' => 'John Doe',
            'subject' => 'Test Subject',
            'message' => $rawMessage,
            'captcha1' => 1,
            'description' => '',
        ]);

        $sanitizer->expects($this->once())
            ->method('sanitizeContactMeMessage')
            ->with($rawMessage)
            ->willReturn($cleanedMessage);
        $templateRendererFactory->expects($this->never())
            ->method('create');

        $builder = new ContactMeBuilder(
            $sanitizer,
            $templateRendererFactory
        );
        $builder->build($data);
    }

    /** @return array<string, array<string, mixed>> */
    public static function builderEmptyMessageDataProvider(): array
    {
        return [
            'empty_message' => [
                'rawMessage' => '',
                'cleanedMessage' => '',
            ],
            'harmful_message' => [
                'rawMessage' => '<script>alert(1)</script>',
                'cleanedMessage' => '',
            ],
        ];
    }
}
