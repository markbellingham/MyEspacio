<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Config;

//use MyEspacio\Music\Domain\MusicRepository;

final class Settings
{
//    private static string $lastFmRefreshDate = '';

    /**
     * @param string $key
     * @return string|array<string, string>
     */
    public static function getConfig(string $key): string|array
    {
        return CONFIG[$key] ?? '';
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
