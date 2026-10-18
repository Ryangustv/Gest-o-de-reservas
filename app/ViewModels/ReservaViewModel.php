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
            'reservas' => $this->reserva->map(fn (ReservaModel $reserva) => [
                'id' => $reserva->id,
                'cliente_nome' => $reserva->cliente_nome,
                'cliente_email' => $reserva->cliente_email,
                'data_reserva' => $reserva->data_reserva,
                'unidade' => [
                    'numero' => $reserva->unidade_numero,
                    'empreendimento' => $reserva->empreendimento_nome,
                ],
            ])->values()->all(),
        ];
    }
}
