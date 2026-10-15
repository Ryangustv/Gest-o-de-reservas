<?php

namespace App\Services;

use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Models\ReservaModel;
use Illuminate\Http\JsonResponse;

class ReservaService
{
    public function __construct(protected ReservaRepositoryInterface $repositoryInterface){}

    public function findById(int $reservaId): ReservaModel
    {
        return $this->repositoryInterface->findById($reservaId);
    }

    public function findAll()
    {
        return $this->repositoryInterface->findAll();
    }

    public function delete(int $reservaId)
    {
        return $this->repositoryInterface->delete($reservaId);
    }
}
