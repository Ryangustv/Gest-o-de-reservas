<?php

namespace Tests\Feature;

use App\Models\EmpreendimentoModel;
use App\Models\ReservaModel;
use App\Models\UnidadeModel;
use App\Services\ReservaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisualizarReservasTest extends TestCase
{
    private function reserva(): ReservaModel
    {
        $empreendimento = new EmpreendimentoModel(['nome' => 'Residencial Jardim']);
        $unidade = new UnidadeModel(['numero' => '101']);
        $unidade->setRelation('empreendimento', $empreendimento);
        $reserva = new ReservaModel([
            'cliente_nome' => 'Ana Silva',
            'cliente_email' => 'ana@example.com',
            'data_reserva' => '2026-10-18',
        ]);
        $reserva->id = 1;
        $reserva->setRelation('unidade', $unidade);

        return $reserva;
    }

    public function test_view_model_sends_reserva_and_unidade_data_to_page(): void
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

    public function test_visitor_can_cancel_reserva_without_authentication(): void
    {
        $this->mock(ReservaService::class, function ($mock) {
            $mock->shouldReceive('delete')->with(1)->once();
        });

        $this->deleteJson('/reservas/1')->assertOk()->assertJson(['success' => true]);
    }

    public function test_nonexistent_reserva_returns_not_found(): void
    {
        $this->mock(ReservaService::class, function ($mock) {
            $mock->shouldReceive('delete')->with(1)->once()
                ->andThrow((new ModelNotFoundException)->setModel(ReservaModel::class, [1]));
        });

        $this->deleteJson('/reservas/1')->assertNotFound();
    }
}
