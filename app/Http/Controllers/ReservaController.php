<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservaModel;
use App\Services\ReservaService;
use App\ViewModels\ReservaViewModel;
use Illuminate\Http\JsonResponse;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;


class ReservaController extends Controller
{
    public function __construct(protected ReservaService $reservaService) {}

    public function buscarReservasIndex() : Response
    {
        $reservas = $this->reservaService->findAll();

        $viewModel = new ReservaViewModel($reservas);

        return Inertia::render('Reservas/index', [
            'reservas' => $viewModel->toArray(),
        ]);
    }

    public function cadastrarReserva(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'unidade_id' => 'required|exists:unidade,id',
            'cliente_nome' => 'required|string|max:255',
            'cliente_email' => 'required|email|max:255',
            'data_reserva' => 'required|date',
        ]);

        $reserva = new ReservaModel($validatedData);
        $reserva = $this->reservaService->create($reserva);

        return response()->json([
            'success' => true,
            'message' => 'Reserva cadastrada com sucesso',
        ]);
    }

    public function deletarReserva(int $reservaId): JsonResponse
    {
        $reserva = $this->reservaService->findById($reservaId);

        Gate::authorize('delete', $reserva);

        $this->reservaService->delete($reservaId);

        return response()->json([
            'success' => true,
            'message' => 'Reserva excluída com sucesso',
        ]);
    }

}
