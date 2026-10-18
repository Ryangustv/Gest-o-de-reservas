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
        return ReservaModel::query()
            ->leftJoin('unidade', 'unidade.id', '=', 'reserva.unidade_id')
            ->leftJoin('empreendimentos', 'empreendimentos.id', '=', 'unidade.empreendimento_id')
            ->select('reserva.*', 'unidade.numero as unidade_numero', 'empreendimentos.nome as empreendimento_nome')
            ->orderByDesc('reserva.id')
            ->get();
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
