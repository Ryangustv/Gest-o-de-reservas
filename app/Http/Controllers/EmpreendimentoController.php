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
   
   public function buscarEmpreendimentos(Request $request): Response|JsonResponse
    {
        $empreendimentos = $this->empreendimentoService->findAll($request->boolean('atualizar'));

        $viewModel = new EmpreendimentosViewModel($empreendimentos);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json($viewModel->toArray());
        }

        return Inertia::render('Empreendimento/index', $viewModel->toArray());
    }
}
