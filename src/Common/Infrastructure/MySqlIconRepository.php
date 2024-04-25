<?php

declare(strict_types=1);

namespace MyEspacio\Common\Infrastructure;

use MyEspacio\Common\Domain\Collection\CaptchaIconCollection;
use MyEspacio\Common\Domain\Repository\IconRepositoryInterface;
use MyEspacio\Framework\Database\Connection;

final class MySqlIconRepository implements IconRepositoryInterface
{
    public function __construct(
        private readonly Connection $db
    ) {
    }

    public function getIcons(int $qty): CaptchaIconCollection
    {
        $result = $this->db->fetchAll(
            'SELECT icon_id, icon, name
            FROM project.icons
            ORDER BY RAND()
            LIMIT :quantity',
            [
                'quantity' => $qty
            ]
        );
        return new CaptchaIconCollection($result);
    }
}
