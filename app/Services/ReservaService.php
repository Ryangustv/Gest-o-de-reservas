<?php

namespace App\Services;

use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;
use App\Models\ReservaModel;
use Illuminate\Http\JsonResponse;

class ReservaService
{
    public function __construct(protected ReservaRepositoryInterface $repositoryInterface,
                                protected UnidadeRepositoryInterface $unidadeRepositoryInterface){}

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

    public function create(ReservaModel $reserva): ReservaModel
    {
        $unidade = $this->unidadeRepositoryInterface->findByUnidadeId($reserva->unidade_id);

        if ($unidade && $unidade->status === 'reservado') {
            return response()->json(['message' => 'A reserva já está reservada e não pode ser criada novamente.'], 400);
        }

        $reserva = $this->repositoryInterface->create($reserva);
        $this->unidadeRepositoryInterface->update($unidade->id, 'reservado');
        return new ReservaModel($reserva);
    }
}
