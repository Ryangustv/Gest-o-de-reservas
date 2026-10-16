<?php

namespace App\ViewModels;

use App\Models\EmpreendimentoModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class EmpreendimentosViewModel extends ViewModel
{
    public function __construct(
        public $empreendimentos
    ) {}

    public function toArray(): array
    {
        return [
            'Empreendimentos' => $this->empreendimentos,
        ];
    }
}
