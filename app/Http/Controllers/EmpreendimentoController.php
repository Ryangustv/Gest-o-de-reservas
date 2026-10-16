<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmpreendimentoService;
use App\ViewModels\EmpreendimentosViewModel;
use App\Models\EmpreendimentoModel;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Inertia\Response;

class EmpreendimentoController extends Controller
{

   public function __construct(protected EmpreendimentoService $empreendimentoService) {}

   public function buscarEmpreendimentos(): Response
    {
        $empreendimentos = $this->empreendimentoService->findAll();

        $viewModel = new EmpreendimentosViewModel($empreendimentos);

        return Inertia::render('Empreendimento/index', [
            'Empreendimento' => $viewModel->toArray(),
        ]);
    }
}
