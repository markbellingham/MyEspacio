<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain;

use MyEspacio\Framework\Model;

final class Relevance extends Model
{
    public function __construct(
        private readonly int $cScore,
        private readonly int $pScore
    ) {
    }

    public function getCScore(): int
    {
        return $this->cScore;
    }

    public function getPScore(): int
    {
        return $this->pScore;
    }
}
