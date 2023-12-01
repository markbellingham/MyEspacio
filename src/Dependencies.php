<?php

declare(strict_types=1);

use Auryn\Injector;
use Personly\Common\Domain\IconsRepository;
use Personly\Common\Infrastructure\MysqlIconsRepository;
use Personly\Framework\Csrf\SymfonySessionTokenStorage;
use Personly\Framework\Csrf\TokenStorage;
use Personly\Framework\Database\Connection;
use Personly\Framework\Database\PdoConnectionFactory;
use Personly\Framework\Rendering\TemplateDirectory;
use Personly\Framework\Rendering\TemplateRenderer;
use Personly\Framework\Rendering\TwigTemplateRendererFactory;
use Personly\Games\Domain\GamesRepository;
use Personly\Games\Infrastructure\MysqlGamesRepository;
use Personly\Music\Application\LastFmMusicHistory;
use Personly\Music\Application\MusicHistory;
use Personly\Music\Domain\MusicRepository;
use Personly\Music\Infrastructure\MysqlMusicRepository;
use Personly\Photos\Infrastructure\MySqlPhotoRepository;
use Personly\Photos\Infrastructure\PhotoRepository;
use Personly\Project\Domain\ProjectRepository;
use Personly\Project\Infrastructure\MysqlProjectRepository;
use Personly\User\Domain\UserRepository;
use Personly\User\Infrastructure\MysqlUserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

$injector = new Injector();

$injector->delegate(
    TemplateRenderer::class,
    function () use ($injector): TemplateRenderer {
        $factory = $injector->make(TwigTemplateRendererFactory::class);
        return $factory->create();
    }
);

$injector->define(TemplateDirectory::class, [':rootDirectory' => ROOT_DIR]);

$injector->alias(TokenStorage::class, SymfonySessionTokenStorage::class);

$injector->alias(SessionInterface::class, Session::class);

$injector->alias(Connection::class, PdoConnectionFactory::class);

$injector->delegate(
    \Personly\Framework\Database\PdoConnection::class,
    function () use ($injector): \Personly\Framework\Database\PdoConnection {
        $factory = $injector->make(PdoConnectionFactory::class);
        return $factory->create();
    }
);

$injector->alias(MusicRepository::class, MysqlMusicRepository::class);

$injector->alias(PhotoRepository::class, MySqlPhotoRepository::class);

$injector->alias(MusicHistory::class, LastFmMusicHistory::class);

$injector->alias(GamesRepository::class, MysqlGamesRepository::class);

$injector->alias(IconsRepository::class, MysqlIconsRepository::class);

$injector->alias(UserRepository::class, MysqlUserRepository::class);

$injector->alias(ProjectRepository::class, MysqlProjectRepository::class);

return $injector;
