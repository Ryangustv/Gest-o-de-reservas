<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\ViewModels\ReservaViewModel;
use Illuminate\Http\JsonResponse;
use Inertia\Response;
use Inertia\Inertia;


class ReservaController extends Controller
{
    public function __construct(protected ReservaService $reservaService) {}

    public function buscarReservasIndex() : Response
    {
        $reservas = $this->reservaService->findAll();

        $viewModel = new ReservaViewModel($reservas);

        return Inertia::render('Reservas/index', $viewModel->toArray());
    }

    public function cadastrarReserva(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'unidade_id' => 'required|integer|exists:unidade,id',
            'cliente_nome' => 'required|string|max:100',
            'cliente_email' => 'required|email|max:100',
        ]);

        $validatedData['data_reserva'] = now()->toDateString();
        $this->reservaService->create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Reserva cadastrada com sucesso',
        ], 201);
    }

    public function deletarReserva(int $reservaId): JsonResponse
    {
        $this->reservaService->delete($reservaId);

        return response()->json([
            'success' => true,
            'message' => 'Reserva excluída com sucesso',
        ]);
    }

}
