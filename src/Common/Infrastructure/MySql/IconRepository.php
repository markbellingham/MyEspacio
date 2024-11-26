<?php

declare(strict_types=1);

namespace MyEspacio\Common\Infrastructure\MySql;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use MyEspacio\Framework\Database\Connection;

final class IconRepository implements IconRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function getIcons(int $quantity): CaptchaIconCollection
    {
        $quantity = max(3, $quantity);
        $result = $this->db->fetchAll(
            'SELECT icon_id, icon, name
            FROM project.icons
            ORDER BY RAND()
            LIMIT ' . $quantity,
            []
        );
        return new CaptchaIconCollection($result);
    }
}
