<?php

namespace App\Repositories\Manager;

use App\Models\UnidadeModel;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;

class UnidadeRepository implements UnidadeRepositoryInterface
{
    public function findByUnidadeId(int $unidadeId): UnidadeModel
    {
        return UnidadeModel::whereKey($unidadeId)->lockForUpdate()->firstOrFail();
    }

    public function update(int $unidadeId, string $status): void
    {
        $unidade = UnidadeModel::findOrFail($unidadeId);
        $unidade->update(['status' => $status]);
    }
}
