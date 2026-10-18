<?php

namespace Tests\Feature;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use App\Services\EmpreendimentoService;
use Tests\TestCase;

class EmpreendimentosCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.stores.redis.driver' => 'array']);
    }

    public function test_atualiza_lista_apos_cinco_minutos(): void
    {
        $this->freezeTime();
        $this->mock(EmpreendimentoRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('findAll')->twice()->andReturn([['id' => 1]], [['id' => 2]]);
        });
        $servico = app(EmpreendimentoService::class);

        $this->assertSame([['id' => 1]], $servico->findAll());
        $this->travel(299)->seconds();
        $this->assertSame([['id' => 1]], $servico->findAll());
        $this->travel(2)->seconds();
        $this->assertSame([['id' => 2]], $servico->findAll());
    }

    public function test_guarda_lista_vazia_no_cache(): void
    {
        $this->mock(EmpreendimentoRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('findAll')->once()->andReturn([]);
        });
        $servico = app(EmpreendimentoService::class);

        $this->assertSame([], $servico->findAll());
        $this->assertSame([], $servico->findAll());
    }
}
