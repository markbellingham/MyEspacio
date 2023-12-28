<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Config;

//use MyEspacio\Music\Domain\MusicRepository;

final class Settings
{
    private static array $dbConfig = [];
    private static string $lastFmRefreshDate = '';

    public function __construct()
    {
        self::$dbConfig = require ROOT_DIR . '/config/config.php';
    }

    public static function getDbConfig(string $key): ?array
    {
        return CONFIG[$key] ?? null;
    }

    public static function getServerSecret(): string
    {
        return CONFIG['server_secret'];
    }

//    public function getLastFmRefreshDate(MusicRepository $musicRepository): string
//    {
//        if (self::$lastFmRefreshDate == '') {
//            $this->setLastFmRefreshDate($musicRepository);
//        }
//        return self::$lastFmRefreshDate;
//    }
//
//    private function setLastFmRefreshDate(MusicRepository $musicRepository): void
//    {
//        self::$lastFmRefreshDate = $musicRepository->getSupplementaryValue('LastFM refresh');
//    }
}