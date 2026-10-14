<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReservaController extends Controller
{   
    public function __construct(protected ReservaService $reservaService) {}

    public function buscarReservasIndex() : Response
    {
        $reservas = $this->reservaService->findAll();

        $viewModel = new ReservaViewModel($reservas);

        return Inertia::render('reservas/index', [
            'reservas' => $viewModel->reservas(),
        ]);
    }

    public function cadastrarReserva(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'data_reserva' => 'required|date',
            'hora_reserva' => 'required|date_format:H:i',
            'quantidade_pessoas' => 'required|integer|min:1',
        ]);

        $reserva = new Reserva($validatedData);
        $reserva->save();

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
