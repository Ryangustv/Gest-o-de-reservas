<?php

namespace App\Services;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;

class EmpreendimentoService
{
    public function __construct(protected EmpreendimentoRepositoryInterface $repositoryInterface){}

    public function findAll()
    {
        $empreendimentos = $this->repositoryInterface->findAll();

        if ($empreendimentos === null) {
            return [];
        }

        return $empreendimentos;
    }
}
