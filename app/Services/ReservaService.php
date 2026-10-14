<?php 

use App\Repositories\Interfaces\ReservaRepositoryInterface;

class ReservaService
{
    public function __construct(protected ReservaRepositoryInterface $repositoryInterface){}

    public function findById(int $reservaId): ?Reserva
    {
        return $this->repositoryInterface->findById($reservaId);
    }

    public function findAll(): Response
    {
        return $this->repositoryInterface->findAll();
    }

    public function delete(int $reservaId): JsonResponse
    {
        return $this->repositoryInterface->delete($reservaId);
    }
}