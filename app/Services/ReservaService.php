<?php

namespace App\Services;

use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;
use App\Models\ReservaModel;
use Illuminate\Http\JsonResponse;
use Exception;

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

    public function create(array $reserva): ReservaModel
    {
        $unidade = $reserva['unidade_id'];

        $existe = ReservaModel::where('unidade_id', $unidade)->exists();

        if ($existe) {
            throw new Exception('Já existe uma reserva para a unidade.');
        }

        return DB::transaction(function () use ($reserva, $unidade) {
            $reserva = $this->repositoryInterface->create($reserva);
            $this->unidadeRepositoryInterface->update($unidade, 'reservado');

            return $reserva;
        });
    }
}
