<?php

namespace App\Repositories\Manager;

use App\Models\ReservaModel;
use App\Repositories\Interfaces\ReservaRepositoryInterface;

class ReservaRepository implements ReservaRepositoryInterface
{

    public function findById(int $reservaId): ReservaModel
    {
        $query = ReservaModel::findOrFail($reservaId);
    }

    public function findAll()
    {
        return ReservaModel::all();
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
        return ReservaModel::create($reserva);
    }
}
