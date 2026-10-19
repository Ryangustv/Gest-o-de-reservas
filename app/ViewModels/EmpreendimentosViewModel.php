<?php

namespace App\ViewModels;

use App\Models\EmpreendimentoModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class EmpreendimentosViewModel extends ViewModel
{
    public function __construct(
        public array $empreendimentos
    ) {}

    public function toArray(): array
    {
        return [
            'empreendimento' => array_map(function ($empreendimento) {
                return [
                    'id' => $empreendimento['id'] ?? null,
                    'nome' => $empreendimento['nome'] ?? null,
                    'cidade' => $empreendimento['cidade'] ?? null,
                    'unidades' => array_map(function ($unidade) {
                        return [
                            'id' => $unidade['id'] ?? null,
                            'numero' => $unidade['numero'] ?? null,
                            'status' => $unidade['status'] ?? null,
                        ];
                    }, $empreendimento['unidades'] ?? []),
                ];
            }, $this->empreendimentos),
        ];
    }
}
