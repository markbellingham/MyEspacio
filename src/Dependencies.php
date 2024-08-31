<?php

declare(strict_types=1);

use Auryn\Injector;
use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use MyEspacio\Common\Domain\Repository\TagRepositoryInterface;
use MyEspacio\Common\Infrastructure\MySql\IconRepository;
use MyEspacio\Common\Infrastructure\MySql\TagRepository;
use MyEspacio\Framework\Csrf\StoredTokenReader;
use MyEspacio\Framework\Csrf\StoredTokenReaderInterface;
use MyEspacio\Framework\Csrf\SymfonySessionTokenStorage;
use MyEspacio\Framework\Csrf\TokenStorage;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\Database\PdoConnection;
use MyEspacio\Framework\Database\PdoConnectionFactory;
use MyEspacio\Framework\Http\ExternalHttpRequestInterface;
use MyEspacio\Framework\Http\GuzzleHttpClient;
use MyEspacio\Framework\Localisation\LanguageLoader;
use MyEspacio\Framework\Localisation\LanguageLoaderInterface;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Localisation\LanguagesDirectoryInterface;
use MyEspacio\Framework\Localisation\NestedArrayReader;
use MyEspacio\Framework\Localisation\NestedArrayReaderInterface;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactory;
use MyEspacio\Framework\Localisation\TranslationIdentifierFactoryInterface;
use MyEspacio\Framework\Logger\LoggerInterface;
use MyEspacio\Framework\Logger\MonologAdapter;
use MyEspacio\Framework\Messages\EmailInterface;
use MyEspacio\Framework\Messages\PhpMailerEmail;
use MyEspacio\Framework\Rendering\TemplateDirectory;
use MyEspacio\Framework\Rendering\TemplateDirectoryInterface;
use MyEspacio\Framework\Rendering\TemplateRendererFactoryInterface;
use MyEspacio\Framework\Rendering\TranslatorFactory;
use MyEspacio\Framework\Rendering\TranslatorFactoryInterface;
use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
use MyEspacio\Photos\Domain\Repository\PhotoAlbumRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoCommentRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoTagRepositoryInterface;
use MyEspacio\Photos\Infrastructure\MySql\PhotoAlbumRepository;
use MyEspacio\Photos\Infrastructure\MySql\PhotoCommentRepository;
use MyEspacio\Photos\Infrastructure\MySql\PhotoRepository;
use MyEspacio\Photos\Infrastructure\MySql\PhotoTagRepository;
use MyEspacio\User\Application\LoginEmailMessage;
use MyEspacio\User\Application\LoginEmailMessageInterface;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

//use MyEspacio\Games\Domain\GamesRepository;
//use MyEspacio\Games\Infrastructure\MysqlGamesRepository;
//use MyEspacio\Music\Application\LastFmMusicHistory;
//use MyEspacio\Music\Application\MusicHistory;
//use MyEspacio\Music\Domain\MusicRepository;
//use MyEspacio\Music\Infrastructure\MysqlMusicRepository;

$injector = new Injector();

$injector->define(TemplateDirectory::class, [':rootDirectory' => ROOT_DIR]);
$injector->alias(TemplateRendererFactoryInterface::class, TwigTemplateRendererFactory::class);
$injector->alias(TranslatorFactoryInterface::class, TranslatorFactory::class);
$injector->alias(TemplateDirectoryInterface::class, TemplateDirectory::class);

$injector->alias(LanguagesDirectoryInterface::class, LanguagesDirectory::class);
$injector->define(LanguagesDirectory::class, [':rootDirectory' => ROOT_DIR]);
$injector->alias(LanguageLoaderInterface::class, LanguageLoader::class);
$injector->alias(NestedArrayReaderInterface::class, NestedArrayReader::class);
$injector->alias(TranslationIdentifierFactoryInterface::class, TranslationIdentifierFactory::class);
//
$injector->alias(TokenStorage::class, SymfonySessionTokenStorage::class);
$injector->alias(StoredTokenReaderInterface::class, StoredTokenReader::class);

$injector->alias(SessionInterface::class, Session::class);

$injector->alias(EmailInterface::class, PhpMailerEmail::class);
$injector->alias(LoginEmailMessageInterface::class, LoginEmailMessage::class);

$injector->delegate(
    Connection::class,
    function () use ($injector): PdoConnection {
        $factory = $injector->make(PdoConnectionFactory::class);
        return $factory->create();
    }
);

$injector->alias(LoggerInterface::class, MonologAdapter::class);

$injector->alias(ExternalHttpRequestInterface::class, GuzzleHttpClient::class);

//$injector->alias(MusicRepository::class, MysqlMusicRepository::class);
//
$injector->alias(PhotoRepositoryInterface::class, PhotoRepository::class);
$injector->alias(PhotoAlbumRepositoryInterface::class, PhotoAlbumRepository::class);
$injector->alias(PhotoCommentRepositoryInterface::class, PhotoCommentRepository::class);
$injector->alias(PhotoTagRepositoryInterface::class, PhotoTagRepository::class);
//
//$injector->alias(MusicHistory::class, LastFmMusicHistory::class);
//
//$injector->alias(GamesRepository::class, MysqlGamesRepository::class);

$injector->alias(IconRepositoryInterface::class, IconRepository::class);
$injector->alias(UserRepositoryInterface::class, UserRepository::class);
$injector->alias(TagRepositoryInterface::class, TagRepository::class);
$injector->alias(CaptchaInterface::class, Captcha::class);

return $injector;
