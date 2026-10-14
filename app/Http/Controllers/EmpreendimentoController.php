<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmpreendimentoService;

class EmpreendimentoController extends Controller
{

   public function __construct(protected EmpreendimentoService $empreendimentoService) {}

   public function buscarEmpreendimentos(): JsonResponse
    {
        $empreendimentos = $this->empreendimentoService->findAll();

        $viewModel = new EmpreendimentosViewModel($empreendimentos);

        return Inertia::render('empreendimentos/index', [
            'empreendimentos' => $viewModel->empreendimentos(),
        ]);
    } 
}
