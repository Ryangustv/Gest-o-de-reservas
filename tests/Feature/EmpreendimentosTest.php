<?php

namespace Tests\Feature;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use App\Services\EmpreendimentoService;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmpreendimentosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.stores.redis.driver' => 'array']);
    }

    private function simularEmpreendimentos(array $empreendimentos): void
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

    public function test_mostra_empreendimentos_e_unidades_na_tela(): void
    {
        $this->withoutVite();
        $this->simularEmpreendimentos($this->empreendimentos());

        $this->get('/empreendimentos')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Empreendimento/index')
            ->has('Empreendimento', 1)
            ->where('Empreendimento.0.nome', 'Residencial Jardim')
            ->has('Empreendimento.0.unidades', 2)
            ->where('Empreendimento.0.unidades.0.status', 'disponivel')
        );
    }

    public function test_retorna_empreendimentos_em_json(): void
    {
        $this->simularEmpreendimentos($this->empreendimentos());

        $this->getJson('/empreendimentos')->assertOk()->assertExactJson([
            'Empreendimento' => $this->empreendimentos(),
        ]);
    }

    public function test_retorna_lista_vazia_quando_nao_ha_empreendimentos(): void
    {
        $this->simularEmpreendimentos([]);

        $this->getJson('/empreendimentos')->assertOk()->assertExactJson(['Empreendimento' => []]);
    }

    public function test_atualizar_lista_substitui_cache_pelos_dados_do_banco(): void
    {
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, [], 300);
        $this->simularEmpreendimentos($this->empreendimentos());

        $this->getJson('/empreendimentos?atualizar=1')->assertOk()->assertExactJson([
            'Empreendimento' => $this->empreendimentos(),
        ]);

        $this->assertSame($this->empreendimentos(), Cache::store('redis')->get(EmpreendimentoService::CACHE_KEY));

        $this->getJson('/empreendimentos')->assertOk()->assertExactJson([
            'Empreendimento' => $this->empreendimentos(),
        ]);
    }

    public function test_acesso_normal_mantem_cache_sem_consultar_banco(): void
    {
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, $this->empreendimentos(), 300);
        $this->mock(EmpreendimentoRepositoryInterface::class, function ($mock) {
            $mock->shouldNotReceive('findAll');
        });

        foreach (['/empreendimentos', '/empreendimentos?atualizar=0'] as $url) {
            $this->getJson($url)->assertOk()->assertExactJson([
                'Empreendimento' => $this->empreendimentos(),
            ]);
        }
    }
}
