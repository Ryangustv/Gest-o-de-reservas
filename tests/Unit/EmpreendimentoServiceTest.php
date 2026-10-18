<?php

namespace Tests\Unit;

use App\Repositories\Interfaces\EmpreendimentoRepositoryInterface;
use App\Services\EmpreendimentoService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;

class EmpreendimentoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-18 12:00:00'));
        $gerenciador = $this->createMock(CacheManager::class);
        $gerenciador->method('store')->with('redis')->willReturn(new Repository(new ArrayStore));
        Cache::swap($gerenciador);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::clearResolvedInstance('cache');

        parent::tearDown();
    }

    public function test_guarda_a_lista_por_cinco_minutos(): void
    {
        $listaAntiga = [['id' => 1]];
        $listaAtualizada = [['id' => 2]];
        $servico = $this->criarServico($listaAntiga, $listaAtualizada);

        $this->assertSame($listaAntiga, $servico->findAll());
        Carbon::setTestNow(Carbon::now()->addSeconds(299));
        $this->assertSame($listaAntiga, $servico->findAll());
        Carbon::setTestNow(Carbon::now()->addSecond());
        $this->assertSame($listaAtualizada, $servico->findAll());
    }

    public function test_guarda_lista_vazia_no_cache(): void
    {
        $servico = $this->criarServico([]);

        $this->assertSame([], $servico->findAll());
        $this->assertSame([], $servico->findAll());
    }

    public function test_atualiza_disponibilidade_apos_limpar_cache(): void
    {
        $servico = $this->criarServico(
            [['id' => 1, 'unidades' => [['id' => 1, 'status' => 'disponivel']]]],
            [['id' => 1, 'unidades' => [['id' => 1, 'status' => 'reservado']]]],
        );
        $this->assertSame('disponivel', $servico->findAll()[0]['unidades'][0]['status']);
        Cache::store('redis')->forget(EmpreendimentoService::CACHE_KEY);
        $this->assertSame('reservado', $servico->findAll()[0]['unidades'][0]['status']);
    }

    private function criarServico(array ...$listas): EmpreendimentoService
    {
        $repositorio = $this->createMock(EmpreendimentoRepositoryInterface::class);
        $repositorio->expects($this->exactly(count($listas)))
            ->method('findAll')->willReturnOnConsecutiveCalls(...$listas);

        return new EmpreendimentoService($repositorio);
    }
}
