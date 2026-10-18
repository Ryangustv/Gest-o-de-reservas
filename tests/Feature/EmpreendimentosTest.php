<?php

namespace Tests\Feature;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmpreendimentosTest extends TestCase
{
    private function mockEmpreendimentos(array $empreendimentos): void
    {
        $this->mock(EmpreendimentoRepositoryInterface::class, function ($mock) use ($empreendimentos) {
            $mock->shouldReceive('findAll')->once()->andReturn($empreendimentos);
        });
    }

    private function empreendimentos(): array
    {
        return [[
            'id' => 1,
            'nome' => 'Residencial Jardim',
            'cidade' => 'São Paulo',
            'unidades' => [
                ['id' => 1, 'numero' => '101', 'status' => 'disponivel'],
                ['id' => 2, 'numero' => '102', 'status' => 'reservado'],
            ],
        ]];
    }

    public function test_route_renders_page_with_empreendimentos_and_unidades(): void
    {
        $this->withoutVite();
        $this->mockEmpreendimentos($this->empreendimentos());

        $this->get('/empreendimentos')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Empreendimento/index')
            ->has('Empreendimento', 1)
            ->where('Empreendimento.0.nome', 'Residencial Jardim')
            ->has('Empreendimento.0.unidades', 2)
            ->where('Empreendimento.0.unidades.0.status', 'disponivel')
        );
    }

    public function test_route_returns_json_for_fetch_requests(): void
    {
        $this->mockEmpreendimentos($this->empreendimentos());

        $this->getJson('/empreendimentos')->assertOk()->assertExactJson([
            'Empreendimento' => $this->empreendimentos(),
        ]);
    }

    public function test_route_returns_an_empty_list_when_no_empreendimentos_exist(): void
    {
        $this->mockEmpreendimentos([]);

        $this->getJson('/empreendimentos')->assertOk()->assertExactJson(['Empreendimento' => []]);
    }
}
