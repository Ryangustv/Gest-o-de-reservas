<?php

namespace App\Services;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class EmpreendimentoService
{
    public const CACHE_KEY = 'empreendimentos.listagem';

    public function __construct(protected EmpreendimentoRepositoryInterface $repositoryInterface){}

    public function findAll(bool $atualizarCache = false)
    {
        if ($atualizarCache) {
            Cache::store('redis')->forget(self::CACHE_KEY);
        }

        return Cache::store('redis')->remember(
            self::CACHE_KEY,
            300,
            fn () => $this->repositoryInterface->findAll() ?? [],
        );
    }
}
