<?php

namespace App\ViewModels;

use App\Models\ReservaModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class ReservaViewModel extends ViewModel
{
    public function __construct(
        public $reserva
    ) {}

        public function toArray(): array
    {
        return [
            'reserva' => $this->reserva,
        ];
    }
}
