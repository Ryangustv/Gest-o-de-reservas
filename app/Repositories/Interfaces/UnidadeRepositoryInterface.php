<?php

namespace App\Repositories\Interfaces;

use App\Models\UnidadeModel;

interface UnidadeRepositoryInterface
{
    public function findByUnidadeId(int $unidadeId): UnidadeModel;

    public function update(int $unidadeId, string $status): void;
}
