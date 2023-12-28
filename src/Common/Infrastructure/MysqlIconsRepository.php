<?php

declare(strict_types=1);

namespace MyEspacio\Common\Infrastructure;

use MyEspacio\Common\Domain\CaptchaIcon;
use MyEspacio\Common\Domain\CaptchaIconCollection;
use MyEspacio\Framework\Database\PdoConnection;

final class MysqlIconsRepository implements IconsRepository
{
    public function __construct(
        private readonly PdoConnection $db
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
