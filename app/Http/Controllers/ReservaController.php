<?php

namespace App\Http\Controllers;

use App\Http\Requests\CadastrarReservaRequest;
use App\Models\ReservaModel;
use App\Services\ReservaService;
use App\ViewModels\ReservaViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function cadastrarReserva(CadastrarReservaRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['data_reserva'] = now()->toDateString();
        $this->reservaService->create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Reserva cadastrada com sucesso',
        ], 201);
    }

    public function deletarReserva(int $reservaId): JsonResponse
    {
        try {
            $this->reservaService->delete($reservaId);
        } catch (ModelNotFoundException $exception) {
            if ($exception->getModel() !== ReservaModel::class) {
                throw $exception;
            }

            return response()->json([
                'success' => false,
                'message' => 'Reserva não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva excluída com sucesso',
        ]);
    }

}
