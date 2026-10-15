<?php

namespace App\Repositories\Interfaces;

use App\Models\ReservaModel;

interface ReservaRepositoryInterface
{
    public function delete(int $reservaId);

    public function findById(int $reservaId): ReservaModel;

    public function findAll();

    public function create(ReservaModel $reserva): ReservaModel;
}
