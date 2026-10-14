<?php

namespace App;

use App\Models\Empreendimento;

class EmpreendimentoRepository implements EmpreendimentoRepositoryInterface
{
    public function findAll(): array{
        return Empreendimento::all()->toArray();
    }
}
 