<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Domain\Entity;

use MyEspacio\Framework\DataSet;
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

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function createFromDataSet(DataSet $data): Relevance
    {
        return new Relevance(
            cScore: $data->int('cscore'),
            pScore: $data->int('pscore')
        );
    }
}
