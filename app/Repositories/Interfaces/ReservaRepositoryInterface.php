<?php

namespace App\Repositories\Interfaces;

use App\Models\ReservaModel;

interface ReservaRepositoryInterface
{
    public function delete(int $reservaId): void;

    public function findById(int $reservaId): ReservaModel;

    public function findAll();

    public function existsByUnidadeId(int $unidadeId): bool;

    public function create(ReservaModel $reserva): ReservaModel;
}
