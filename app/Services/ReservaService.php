<?php

namespace App\Services;

use App\Models\ReservaModel;
use App\Repositories\Interfaces\ReservaRepositoryInterface;

class ReservaService
{
    public function __construct(protected ReservaRepositoryInterface $repositoryInterface) {}

    public function findById(int $reservaId): ReservaModel
    {
        return $this->repositoryInterface->findById($reservaId);
    }

    public function findAll()
    {
        return $this->repositoryInterface->findAll();
    }

    public function delete(int $reservaId): void
    {
        $this->repositoryInterface->delete($reservaId);
    }

    public function create(array $reserva): ReservaModel
    {
        return $this->repositoryInterface->create(new ReservaModel($reserva));
    }
}
