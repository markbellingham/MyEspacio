<?php

declare(strict_types=1);

use Auryn\Injector;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use MyEspacio\Common\Domain\Repository\TagRepositoryInterface;
use MyEspacio\Common\Infrastructure\MySqlIconRepository;
use MyEspacio\Common\Infrastructure\MySqlTagRepository;
use MyEspacio\Framework\Csrf\SymfonySessionTokenStorage;
use MyEspacio\Framework\Csrf\TokenStorage;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\Database\PdoConnection;
use MyEspacio\Framework\Database\PdoConnectionFactory;
use MyEspacio\Framework\Http\ExternalHttpRequestInterface;
use MyEspacio\Framework\Http\GuzzleHttpClient;
use MyEspacio\Framework\Localisation\LanguagesDirectory;
use MyEspacio\Framework\Logger\LoggerInterface;
use MyEspacio\Framework\Logger\MonologAdapter;
use MyEspacio\Framework\Messages\EmailInterface;
use MyEspacio\Framework\Messages\PhpMailerEmail;
use MyEspacio\Framework\Rendering\TemplateDirectory;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Infrastructure\MysqlUserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

//use MyEspacio\Games\Domain\GamesRepository;
//use MyEspacio\Games\Infrastructure\MysqlGamesRepository;
//use MyEspacio\Music\Application\LastFmMusicHistory;
//use MyEspacio\Music\Application\MusicHistory;
//use MyEspacio\Music\Domain\MusicRepository;
//use MyEspacio\Music\Infrastructure\MysqlMusicRepository;
//use MyEspacio\Photos\Infrastructure\MySqlPhotoRepository;
//use MyEspacio\Photos\Infrastructure\PhotoRepository;

$injector = new Injector();

$injector->define(TemplateDirectory::class, [':rootDirectory' => ROOT_DIR]);

$injector->define(LanguagesDirectory::class, [':rootDirectory' => ROOT_DIR]);
//
$injector->alias(TokenStorage::class, SymfonySessionTokenStorage::class);

$injector->alias(SessionInterface::class, Session::class);

$injector->alias(EmailInterface::class, PhpMailerEmail::class);

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
//$injector->alias(PhotoRepository::class, MySqlPhotoRepository::class);
//
//$injector->alias(MusicHistory::class, LastFmMusicHistory::class);
//
//$injector->alias(GamesRepository::class, MysqlGamesRepository::class);

$injector->alias(IconRepositoryInterface::class, MySqlIconRepository::class);

$injector->alias(UserRepositoryInterface::class, MysqlUserRepository::class);

$injector->alias(TagRepositoryInterface::class, MySqlTagRepository::class);

return $injector;
