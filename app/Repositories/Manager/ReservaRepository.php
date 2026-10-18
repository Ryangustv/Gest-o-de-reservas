<?php

namespace App\Repositories\Manager;

use App\Models\ReservaModel;
use App\Repositories\Interfaces\ReservaRepositoryInterface;

class ReservaRepository implements ReservaRepositoryInterface
{

    public function findById(int $reservaId): ReservaModel
    {
        return ReservaModel::findOrFail($reservaId);
    }

    public function findAll()
    {
        return ReservaModel::with('unidade.empreendimento')->get();
    }

    public function delete(int $reservaId)
    {
        $reserva = ReservaModel::findOrFail($reservaId);
        if ($reserva) {
            $reserva->delete();
        }
    }

    public function create(ReservaModel $reserva): ReservaModel
    {
        $reserva->save();

        return $reserva;
    }
}
