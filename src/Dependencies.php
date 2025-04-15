<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use MyEspacio\Common\Application\Captcha;
use MyEspacio\Common\Application\CaptchaInterface;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use MyEspacio\Common\Domain\Repository\TagRepositoryInterface;
use MyEspacio\Common\Infrastructure\MySql\IconRepository;
use MyEspacio\Common\Infrastructure\MySql\TagRepository;
use MyEspacio\Framework\Csrf\StoredTokenReader;
use MyEspacio\Framework\Csrf\StoredTokenReaderInterface;
use MyEspacio\Framework\Csrf\StoredTokenValidator;
use MyEspacio\Framework\Csrf\StoredTokenValidatorInterface;
use MyEspacio\Framework\Csrf\SymfonySessionTokenStorage;
use MyEspacio\Framework\Csrf\TokenStorage;
use MyEspacio\Framework\Database\Connection;
use MyEspacio\Framework\Database\PdoConnectionFactory;
use MyEspacio\Framework\Http\ExternalHttpRequestInterface;
use MyEspacio\Framework\Http\GuzzleHttpClient;
use MyEspacio\Framework\Http\RequestHandler;
use MyEspacio\Framework\Http\RequestHandlerInterface;
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
use MyEspacio\Photos\Application\PhotoSearch;
use MyEspacio\Photos\Application\PhotoSearchInterface;
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
use MyEspacio\User\Application\SendLoginCode;
use MyEspacio\User\Application\SendLoginCodeInterface;
use MyEspacio\User\Domain\UserRepositoryInterface;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

require ROOT_DIR . '/vendor/autoload.php';

$builder = new ContainerBuilder();

// Optional: Enable compilation for performance in production
// $builder->enableCompilation(ROOT_DIR . '/var/cache/php-di');

$builder->addDefinitions([
    // Core framework
    TemplateRendererFactoryInterface::class => DI\autowire(TwigTemplateRendererFactory::class),
    TranslatorFactoryInterface::class => DI\autowire(TranslatorFactory::class),
    TemplateDirectoryInterface::class => DI\create(TemplateDirectory::class)
        ->constructor(ROOT_DIR),

    LanguagesDirectoryInterface::class => DI\create(LanguagesDirectory::class)
        ->constructor(ROOT_DIR),
    LanguageLoaderInterface::class => DI\autowire(LanguageLoader::class),
    NestedArrayReaderInterface::class => DI\autowire(NestedArrayReader::class),
    TranslationIdentifierFactoryInterface::class => DI\autowire(TranslationIdentifierFactory::class),

    TokenStorage::class => DI\autowire(SymfonySessionTokenStorage::class),
    StoredTokenReaderInterface::class => DI\autowire(StoredTokenReader::class),
    StoredTokenValidatorInterface::class => DI\autowire(StoredTokenValidator::class),

    SessionInterface::class => DI\autowire(Session::class),

    EmailInterface::class => DI\autowire(PhpMailerEmail::class),
    LoginEmailMessageInterface::class => DI\autowire(LoginEmailMessage::class),
    SendLoginCodeInterface::class => DI\autowire(SendLoginCode::class),

    LoggerInterface::class => DI\autowire(MonologAdapter::class),

    RequestHandlerInterface::class => DI\autowire(RequestHandler::class),
    ExternalHttpRequestInterface::class => DI\autowire(GuzzleHttpClient::class),

    PhotoRepositoryInterface::class => DI\autowire(PhotoRepository::class),
    PhotoAlbumRepositoryInterface::class => DI\autowire(PhotoAlbumRepository::class),
    PhotoCommentRepositoryInterface::class => DI\autowire(PhotoCommentRepository::class),
    PhotoTagRepositoryInterface::class => DI\autowire(PhotoTagRepository::class),
    PhotoSearchInterface::class => DI\autowire(PhotoSearch::class),

    IconRepositoryInterface::class => DI\autowire(IconRepository::class),
    UserRepositoryInterface::class => DI\autowire(UserRepository::class),
    TagRepositoryInterface::class => DI\autowire(TagRepository::class),
    CaptchaInterface::class => DI\autowire(Captcha::class),

    Connection::class => DI\factory(function (PdoConnectionFactory $factory) {
        return $factory->create();
    }),
]);

$container = $builder->build();
return $container;
