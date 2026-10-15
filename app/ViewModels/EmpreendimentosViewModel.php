<?php

namespace App\ViewModels;

use App\Models\EmpreendimentoModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class EmpreendimentoViewModel extends ViewModel
{
    public function __construct(
        public ?LengthAwarePaginator $empreendimentos = null,
        public ?EmpreendimentoModel $empreendimento = null
    ) {}
}
