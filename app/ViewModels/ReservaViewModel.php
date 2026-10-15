<?php

namespace App\ViewModels;

use App\Models\ReservaModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class ReservaViewModel extends ViewModel
{
    public function __construct(
        public ?LengthAwarePaginator $reservas = null,
        public ?ReservaModel $reserva = null
    ) {}
}
