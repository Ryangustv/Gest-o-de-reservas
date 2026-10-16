<?php

namespace App\Repositories\Manager;

use App\Models\EmpreendimentoModel;
use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;

class EmpreendimentoRepository implements EmpreendimentoRepositoryInterface
{
    public function findAll(): array{
        return EmpreendimentoModel::with('unidades')->get()->toArray();
    }
}
