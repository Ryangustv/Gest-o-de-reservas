<?php

namespace App\Services;

use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;
use App\Models\ReservaModel;
use App\Models\UnidadeModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

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
        return DB::transaction(function () use ($reservaId) {
            $reserva = $this->repositoryInterface->findById($reservaId);
            $unidade = UnidadeModel::whereKey($reserva->unidade_id)->lockForUpdate()->firstOrFail();

            $this->repositoryInterface->delete($reservaId);
            $this->unidadeRepositoryInterface->update($unidade->id, 'disponivel');
            DB::afterCommit(fn () => Cache::store('redis')->forget(EmpreendimentoService::CACHE_KEY));
        });
    }

    public function create(array $reserva): ReservaModel
    {
        return DB::transaction(function () use ($reserva) {
            $unidade = UnidadeModel::whereKey($reserva['unidade_id'])->lockForUpdate()->firstOrFail();

            if ($unidade->status !== 'disponivel' || ReservaModel::where('unidade_id', $unidade->id)->exists()) {
                throw ValidationException::withMessages([
                    'unidade_id' => 'Esta unidade não está mais disponível para reserva.',
                ]);
            }

            $reserva = $this->repositoryInterface->create(new ReservaModel($reserva));
            $this->unidadeRepositoryInterface->update($unidade->id, 'reservado');
            DB::afterCommit(fn () => Cache::store('redis')->forget(EmpreendimentoService::CACHE_KEY));

            return $reserva;
        });
    }
}
