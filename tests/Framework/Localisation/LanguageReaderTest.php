<?php

declare(strict_types=1);

namespace Tests\Framework\Localisation;

use MyEspacio\Framework\Localisation\LanguageLoader;
use MyEspacio\Framework\Localisation\LanguageReader;
use MyEspacio\Framework\Localisation\NestedArrayReader;
use MyEspacio\Framework\Localisation\TranslationIdentifier;
use PHPUnit\Framework\TestCase;

final class LanguageReaderTest extends TestCase
{
    public function testGetTranslationText()
    {
        $languageLoader = $this->createMock(LanguageLoader::class);
        $reader = $this->createMock(NestedArrayReader::class);
        $identifier = $this->createMock(TranslationIdentifier::class);

        $reader->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $reader->expects($this->once())
            ->method('getValue')
            ->with(['login', 'logged_in'])
            ->willReturn('You are now logged in');

        $languageReader = new LanguageReader($languageLoader, $reader);
        $result = $languageReader->getTranslationText($identifier, 'login.logged_in');
        $this->assertEquals('You are now logged in', $result);
    }

    public function testGetTranslationTextWithVariables()
    {
        $languageLoader = $this->createMock(LanguageLoader::class);
        $reader = $this->createMock(NestedArrayReader::class);
        $identifier = $this->createMock(TranslationIdentifier::class);

        $reader->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $reader->expects($this->once())
            ->method('getValue')
            ->with(['login', 'code_sent'])
            ->willReturn('Please check your %{passcode_route} for the login code');

        $languageReader = new LanguageReader($languageLoader, $reader);
        $result = $languageReader->getTranslationText($identifier, 'login.code_sent', ['passcode_route' => 'email']);
        $this->assertEquals('Please check your email for the login code', $result);
    }

    public function testGetTranslationTextNotFound()
    {
        $languageLoader = $this->createMock(LanguageLoader::class);
        $reader = $this->createMock(NestedArrayReader::class);
        $identifier = $this->createMock(TranslationIdentifier::class);

        $reader->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $reader->expects($this->once())
            ->method('getValue')
            ->with(['login', 'bad_data'])
            ->willReturn(null);

        $languageReader = new LanguageReader($languageLoader, $reader);
        $result = $languageReader->getTranslationText($identifier, 'login.bad_data');
        $this->assertEquals('', $result);
    }

    public function testGetTranslationTextNoInitialData()
    {
        $languageLoader = $this->createMock(LanguageLoader::class);
        $reader = $this->createMock(NestedArrayReader::class);
        $identifier = $this->createMock(TranslationIdentifier::class);

        $reader->expects($this->once())
            ->method('hasData')
            ->willReturn(false);
        $identifier->expects($this->once())
            ->method('getLanguage')
            ->willReturn('en');
        $identifier->expects($this->once())
            ->method('getFilename')
            ->willReturn('messages');
        $languageLoader->expects($this->once())
            ->method('loadTranslations')
            ->with('en', 'messages')
            ->willReturn(
                [
                    'login' => [
                        'already_logged_in' => 'You are already logged in',
                        'generic_error' => 'Something went wrong, please contact the website administrator',
                        'invalid_link' => 'Invalid link. Please try to log in again.',
                        'logged_in' => 'You are now logged in',
                        'logged_out' => 'You are now logged out',
                        'code_sent' => 'Please check your %{passcode_route} for the login code',
                        'error' => 'Could not log you in.',
                        'user_not_found' => 'User not found',
                    ]
                ]
            );
        $reader->expects($this->once())
            ->method('getValue')
            ->with(['login', 'invalid_link'])
            ->willReturn('Invalid link. Please try to log in again.');

        $languageReader = new LanguageReader($languageLoader, $reader);
        $result = $languageReader->getTranslationText($identifier, 'login.invalid_link');
        $this->assertEquals('Invalid link. Please try to log in again.', $result);
    }
}
