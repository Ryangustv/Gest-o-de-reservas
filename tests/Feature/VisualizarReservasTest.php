<?php

namespace Tests\Feature;

use App\Models\ReservaModel;
use App\Repositories\Interfaces\ReservaRepositoryInterface;
use App\Services\ReservaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisualizarReservasTest extends TestCase
{
    private function reserva(): ReservaModel
    {
        $reserva = new ReservaModel([
            'cliente_nome' => 'Ana Silva',
            'cliente_email' => 'ana@example.com',
            'data_reserva' => '2026-10-18',
        ]);
        $reserva->id = 1;
        $reserva->unidade_numero = '101';
        $reserva->empreendimento_nome = 'Residencial Jardim';

        return $reserva;
    }

    public function test_mostra_reservas_e_unidades_na_tela(): void
    {
        $this->withoutVite();
        $reserva = $this->reserva();
        $this->mock(ReservaService::class, function ($mock) use ($reserva) {
            $mock->shouldReceive('findAll')->once()->andReturn(collect([$reserva]));
        });

        $this->get('/reservas')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Reservas/index')
            ->has('reservas', 1)
            ->where('reservas.0.cliente_nome', 'Ana Silva')
            ->where('reservas.0.cliente_email', 'ana@example.com')
            ->where('reservas.0.data_reserva', '2026-10-18')
            ->where('reservas.0.unidade.numero', '101')
            ->where('reservas.0.unidade.empreendimento', 'Residencial Jardim')
        );
    }

    public function test_visitante_pode_cancelar_reserva(): void
    {
        $this->mock(ReservaService::class, function ($mock) {
            $mock->shouldReceive('delete')->with(1)->once();
        });

        $this->deleteJson('/reservas/1')->assertOk()->assertJson(['success' => true]);
    }

    public function test_retorna_404_para_reserva_inexistente(): void
    {
        $this->mock(ReservaService::class, function ($mock) {
            $mock->shouldReceive('delete')->with(1)->once()
                ->andThrow((new ModelNotFoundException)->setModel(ReservaModel::class, [1]));
        });

        $this->deleteJson('/reservas/1')->assertNotFound()->assertExactJson([
            'success' => false,
            'message' => 'Reserva não encontrada.',
        ]);
    }

    public function test_service_propaga_reserva_inexistente_do_repository(): void
    {
        $this->mock(ReservaRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('delete')->with(999)->once()
                ->andThrow((new ModelNotFoundException)->setModel(ReservaModel::class, [999]));
        });

        $this->deleteJson('/reservas/999')->assertNotFound()->assertExactJson([
            'success' => false,
            'message' => 'Reserva não encontrada.',
        ]);
    }
}
