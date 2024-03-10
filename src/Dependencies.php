<?php

declare(strict_types=1);

use Auryn\Injector;
use MyEspacio\Common\Infrastructure\IconsRepository;
use MyEspacio\Common\Infrastructure\MysqlIconsRepository;
//use MyEspacio\Framework\Csrf\SymfonySessionTokenStorage;
//use MyEspacio\Framework\Csrf\TokenStorage;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\Database\PdoConnection;
use MyEspacio\Framework\Database\PdoConnectionFactory;
//use MyEspacio\Framework\Rendering\TemplateDirectory;
//use MyEspacio\Framework\Rendering\TemplateRenderer;
//use MyEspacio\Framework\Rendering\TwigTemplateRendererFactory;
//use MyEspacio\Games\Domain\GamesRepository;
//use MyEspacio\Games\Infrastructure\MysqlGamesRepository;
//use MyEspacio\Music\Application\LastFmMusicHistory;
//use MyEspacio\Music\Application\MusicHistory;
//use MyEspacio\Music\Domain\MusicRepository;
//use MyEspacio\Music\Infrastructure\MysqlMusicRepository;
//use MyEspacio\Photos\Infrastructure\MySqlPhotoRepository;
//use MyEspacio\Photos\Infrastructure\PhotoRepository;
//use MyEspacio\Project\Domain\ProjectRepository;
//use MyEspacio\Project\Infrastructure\MysqlProjectRepository;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Infrastructure\MysqlUserRepository;
use MyEspacio\Framework\Messages\PhpMailerEmail;
use MyEspacio\Framework\Messages\Email;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

$injector = new Injector();

//$injector->delegate(
//    TemplateRenderer::class,
//    function () use ($injector): TemplateRenderer {
//        $factory = $injector->make(TwigTemplateRendererFactory::class);
//        return $factory->create();
//    }
//);
//
//$injector->define(TemplateDirectory::class, [':rootDirectory' => ROOT_DIR]);
//
//$injector->alias(TokenStorage::class, SymfonySessionTokenStorage::class);

$injector->alias(SessionInterface::class, Session::class);

$injector->alias(Email::class, PhpMailerEmail::class);

$injector->delegate(
    Connection::class,
    function () use ($injector): PdoConnection {
        $factory = $injector->make(PdoConnectionFactory::class);
        return $factory->create();
    }
);

//$injector->alias(MusicRepository::class, MysqlMusicRepository::class);
//
//$injector->alias(PhotoRepository::class, MySqlPhotoRepository::class);
//
//$injector->alias(MusicHistory::class, LastFmMusicHistory::class);
//
//$injector->alias(GamesRepository::class, MysqlGamesRepository::class);
//
$injector->alias(IconsRepository::class, MysqlIconsRepository::class);
//
$injector->alias(UserRepositoryInterface::class, MysqlUserRepository::class);
//
//$injector->alias(ProjectRepository::class, MysqlProjectRepository::class);

return $injector;
