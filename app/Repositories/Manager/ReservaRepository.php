<?php

namespace App\Repositories\Manager;

use App\Models\ReservaModel;
use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Repositories\Interfaces\UnidadeRepositoryInterface;
use App\Services\EmpreendimentoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaRepository implements ReservaRepositoryInterface
{
    public function __construct(protected UnidadeRepositoryInterface $unidadeRepositoryInterface) {}

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

    public function delete(int $reservaId): void
    {
        DB::transaction(function () use ($reservaId): void {
            $reserva = ReservaModel::whereKey($reservaId)->lockForUpdate()->firstOrFail();
            $unidade = $this->unidadeRepositoryInterface->findByUnidadeId($reserva->unidade_id);

            $reserva->delete();
            $this->unidadeRepositoryInterface->update($unidade->id, 'disponivel');

            DB::afterCommit(fn () => Cache::store('redis')->forget(EmpreendimentoService::CACHE_KEY));
        });
    }

    public function create(ReservaModel $reserva): ReservaModel
    {
        return DB::transaction(function () use ($reserva): ReservaModel {
            $unidade = $this->unidadeRepositoryInterface->findByUnidadeId($reserva->unidade_id);

            if ($unidade->status !== 'disponivel' || $this->existsByUnidadeId($unidade->id)) {
                throw ValidationException::withMessages([
                    'unidade_id' => 'Esta unidade não está mais disponível para reserva.',
                ]);
            }

            $reserva->save();
            $this->unidadeRepositoryInterface->update($unidade->id, 'reservado');
            DB::afterCommit(fn () => Cache::store('redis')->forget(EmpreendimentoService::CACHE_KEY));

            return $reserva;
        });
    }
}
