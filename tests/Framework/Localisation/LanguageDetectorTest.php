<?php

declare(strict_types=1);

namespace Tests\Framework\Localisation;

use MyEspacio\Framework\Localisation\Language;
use MyEspacio\Framework\Localisation\LanguageDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class LanguageDetectorTest extends TestCase
{
    public function testUrlLanguagePattern(): void
    {
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/en');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/es');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/fr');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/en/');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/es/');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/fr/');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/en/contact');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/es/contact');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/fr/contact');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/en/photos');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/es/photos');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/fr/photos');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/EN/');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/En/photos');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/de/contact');
        $this->assertMatchesRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/it/photos');

        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/contact');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/photos');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/e/');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/eng/photos');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/1n/photos');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/e$/photos');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/enphotos');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/frcontact');
        $this->assertDoesNotMatchRegularExpression(LanguageDetector::URL_LANGUAGE_PATTERN, '/photos/en/');
    }

    #[DataProvider('getFromPathDataProvider')]
    public function testGetFromPath(
        Request $request,
        Language $expectedLanguage,
    ): void {
        $languageDetector = new LanguageDetector(Language::cases());
        $this->assertEquals($expectedLanguage, $languageDetector->getFromPath($request));
    }

    /** @return array<string, array<string, mixed>> */
    public static function getFromPathDataProvider(): array
    {
        return [
            'no_path' => [
                'request' => self::request(),
                'expectedLanguage' => Language::EN,
            ],
            'no_language_prefix_1' => [
                'request' => self::request('/photos'),
                'expectedLanguage' => Language::EN,
            ],
            'no_language_prefix_2' => [
                'request' => self::request('/contact'),
                'expectedLanguage' => Language::EN,
            ],
            'language_prefix_en_1' => [
                'request' => self::request('/en'),
                'expectedLanguage' => Language::EN,
            ],
            'language_prefix_en_2' => [
                'request' => self::request('/en/photos'),
                'expectedLanguage' => Language::EN,
            ],
            'language_prefix_en_3' => [
                'request' => self::request('/en/contact'),
                'expectedLanguage' => Language::EN,
            ],
            'language_prefix_es_1' => [
                'request' => self::request('/es'),
                'expectedLanguage' => Language::ES,
            ],
            'language_prefix_es_2' => [
                'request' => self::request('/es/photos'),
                'expectedLanguage' => Language::ES,
            ],
            'language_prefix_fr_1' => [
                'request' => self::request('/fr'),
                'expectedLanguage' => Language::FR,
            ],
            'language_prefix_fr_2' => [
                'request' => self::request('/fr/photos'),
                'expectedLanguage' => Language::FR,
            ],
        ];
    }

    #[DataProvider('removeLanguagePrefixDataProvider')]
    public function testRemoveLanguagePrefix(
        Request $request,
        string $expectedPath,
    ): void {
        $languageDetector = new LanguageDetector(Language::cases());
        $this->assertEquals($expectedPath, $languageDetector->removeLanguagePrefix($request));
    }

    /** @return array<string, array<string, mixed>> */
    public static function removeLanguagePrefixDataProvider(): array
    {
        return [
            'no_path' => [
                'request' => self::request(),
                'expectedPath' => '/',
            ],
            'no_language_prefix_1' => [
                'request' => self::request('/photos'),
                'expectedPath' => '/photos',
            ],
            'no_language_prefix_2' => [
                'request' => self::request('/contact'),
                'expectedPath' => '/contact',
            ],
            'language_prefix_en_1' => [
                'request' => self::request('/en'),
                'expectedPath' => '/',
            ],
            'language_prefix_en_2' => [
                'request' => self::request('/en/photos'),
                'expectedPath' => '/photos',
            ],
            'language_prefix_en_3' => [
                'request' => self::request('/en/contact'),
                'expectedPath' => '/contact',
            ],
            'language_prefix_es_1' => [
                'request' => self::request('/es'),
                'expectedPath' => '/',
            ],
            'language_prefix_es_2' => [
                'request' => self::request('/es/photos'),
                'expectedPath' => '/photos',
            ],
            'language_prefix_fr_1' => [
                'request' => self::request('/fr'),
                'expectedPath' => '/',
            ],
            'language_prefix_fr_2' => [
                'request' => self::request('/fr/photos'),
                'expectedPath' => '/photos',
            ],
        ];
    }


    private static function request(string $uri = '/'): Request
    {
        return new Request([], [], [], [], [], [
            'REQUEST_URI' => $uri,
        ]);
    }
}
