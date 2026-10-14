<?php

namespace App;

class ReservaRepository implements ReservaRepositoryInterface
{

    public function findById(int $reservaId): ?Reserva
    {
        return Reserva::findOrFail($reservaId);
    }  

    public function findAll(): Response
    {
        return Reserva::all()->toArray();
    }

    public function delete(int $reservaId): JsonResponse
    {
        $reserva = Reserva::findOrFail($reservaId);
        if ($reserva) {
            $reserva->delete();
        }
    }
}
 