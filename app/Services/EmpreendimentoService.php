<?php

namespace App\Services;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;

class EmpreendimentoService
{
    public function __construct(protected EmpreendimentoRepositoryInterface $repositoryInterface){}

    public function findAll()
    {
        return $this->repositoryInterface->findAll();
    }
}
