<?php

namespace Tests\Feature;

use App\Models\EmpreendimentoModel;
use App\Models\ReservaModel;
use App\Models\UnidadeModel;
use App\Repositories\Manager\ReservaRepository;
use App\Services\EmpreendimentoService;
use App\ViewModels\ReservaViewModel;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class CadastrarReservaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Os testes usam SQLite em memória; habilite pdo_sqlite.');
        }

        parent::setUp();
        config(['cache.stores.redis.driver' => 'array']);
    }

    private function unidade(string $status = 'disponivel'): UnidadeModel
    {
        $empreendimento = EmpreendimentoModel::create(['nome' => 'Residencial Jardim', 'cidade' => 'São Paulo']);

        return UnidadeModel::create([
            'empreendimento_id' => $empreendimento->id,
            'numero' => '101',
            'status' => $status,
        ]);
    }

    private function dados(UnidadeModel $unidade): array
    {
        return ['unidade_id' => $unidade->id, 'cliente_nome' => 'Ana Silva', 'cliente_email' => 'ana@example.com'];
    }

    public function test_lista_reservas_mais_recentes_primeiro(): void
    {
        $primeira = ReservaModel::create([
            ...$this->dados($this->unidade()),
            'data_reserva' => '2026-10-18',
        ]);
        $segunda = ReservaModel::create([
            ...$this->dados($this->unidade()),
            'data_reserva' => '2026-10-18',
        ]);

        DB::enableQueryLog();
        $reservas = app(ReservaRepository::class)->findAll();
        $dados = (new ReservaViewModel($reservas))->toArray();

        $this->assertSame([$segunda->id, $primeira->id], array_column($dados['reservas'], 'id'));
        $this->assertSame('101', $dados['reservas'][0]['unidade']['numero']);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_cadastra_reserva_e_marca_unidade_como_reservada(): void
    {
        $unidade = $this->unidade();
        $dados = $this->dados($unidade);
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);
        $nivelTransacaoInicial = DB::transactionLevel();
        $cacheInvalidado = false;
        Event::listen(KeyForgotten::class, function (KeyForgotten $evento) use ($unidade, $nivelTransacaoInicial, &$cacheInvalidado): void {
            if ($evento->key !== EmpreendimentoService::CACHE_KEY) {
                return;
            }

            $cacheInvalidado = true;
            $this->assertSame($nivelTransacaoInicial, DB::transactionLevel());
            $this->assertDatabaseHas('reserva', ['unidade_id' => $unidade->id]);
            $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
        });

        $this->postJson('/reservas', $dados)->assertCreated()->assertJson(['success' => true]);

        $this->assertDatabaseHas('reserva', [...$dados, 'data_reserva' => now()->toDateString()]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
        $this->assertTrue($cacheInvalidado);
        $this->assertFalse(Cache::store('redis')->has(EmpreendimentoService::CACHE_KEY));
    }

    public function test_cancelamento_limpa_cache_e_libera_unidade(): void
    {
        $unidade = $this->unidade('reservado');
        $reserva = ReservaModel::create([...$this->dados($unidade), 'data_reserva' => now()->toDateString()]);
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);
        $nivelTransacaoInicial = DB::transactionLevel();
        $cacheInvalidado = false;
        Event::listen(KeyForgotten::class, function (KeyForgotten $evento) use ($unidade, $reserva, $nivelTransacaoInicial, &$cacheInvalidado): void {
            if ($evento->key !== EmpreendimentoService::CACHE_KEY) {
                return;
            }

            $cacheInvalidado = true;
            $this->assertSame($nivelTransacaoInicial, DB::transactionLevel());
            $this->assertDatabaseMissing('reserva', ['id' => $reserva->id]);
            $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
        });

        $this->deleteJson('/reservas/'.$reserva->id)->assertOk();

        $this->assertDatabaseMissing('reserva', ['id' => $reserva->id]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
        $this->assertTrue($cacheInvalidado);
        $this->assertFalse(Cache::store('redis')->has(EmpreendimentoService::CACHE_KEY));
    }

    public function test_falha_ao_atualizar_unidade_desfaz_criacao_e_preserva_cache(): void
    {
        $unidade = $this->unidade();
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);
        $falhouAtualizacao = false;
        Event::listen('eloquent.updated: '.UnidadeModel::class, function () use (&$falhouAtualizacao): void {
            $falhouAtualizacao = true;
            throw new RuntimeException('Falha ao atualizar a unidade.');
        });

        $this->postJson('/reservas', $this->dados($unidade))->assertInternalServerError();

        $this->assertTrue($falhouAtualizacao);
        $this->assertDatabaseCount('reserva', 0);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
        $this->assertSame(['dados anteriores'], Cache::store('redis')->get(EmpreendimentoService::CACHE_KEY));
    }

    public function test_falha_ao_atualizar_unidade_desfaz_cancelamento_e_preserva_cache(): void
    {
        $unidade = $this->unidade('reservado');
        $dados = [...$this->dados($unidade), 'data_reserva' => now()->toDateString()];
        $reserva = ReservaModel::create($dados);
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);
        $falhouAtualizacao = false;
        Event::listen('eloquent.updated: '.UnidadeModel::class, function () use (&$falhouAtualizacao): void {
            $falhouAtualizacao = true;
            throw new RuntimeException('Falha ao atualizar a unidade.');
        });

        $this->deleteJson('/reservas/'.$reserva->id)->assertInternalServerError();

        $this->assertTrue($falhouAtualizacao);
        $this->assertDatabaseHas('reserva', ['id' => $reserva->id, ...$dados]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
        $this->assertSame(['dados anteriores'], Cache::store('redis')->get(EmpreendimentoService::CACHE_KEY));
    }

    public function test_nao_altera_unidade_nem_cache_quando_reserva_nao_existe(): void
    {
        $unidade = $this->unidade('reservado');
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);

        $this->deleteJson('/reservas/999')->assertNotFound()->assertExactJson([
            'success' => false,
            'message' => 'Reserva não encontrada.',
        ]);

        $this->assertDatabaseCount('reserva', 0);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
        $this->assertSame(['dados anteriores'], Cache::store('redis')->get(EmpreendimentoService::CACHE_KEY));
    }

    public function test_nao_permite_reservar_unidade_ja_reservada(): void
    {
        $unidade = $this->unidade('reservado');

        $this->postJson('/reservas', $this->dados($unidade))->assertUnprocessable()->assertJsonValidationErrors('unidade_id');

        $this->assertDatabaseCount('reserva', 0);
    }

    public function test_nao_permite_duas_reservas_para_mesma_unidade(): void
    {
        $unidade = $this->unidade();
        ReservaModel::create([...$this->dados($unidade), 'data_reserva' => now()->toDateString()]);
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);

        $this->postJson('/reservas', $this->dados($unidade))->assertUnprocessable()->assertJsonValidationErrors('unidade_id');

        $this->assertDatabaseCount('reserva', 1);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
        $this->assertSame(['dados anteriores'], Cache::store('redis')->get(EmpreendimentoService::CACHE_KEY));
    }

    public function test_dados_invalidos_nao_reservam_unidade(): void
    {
        $unidade = $this->unidade();

        $this->postJson('/reservas', [
            'unidade_id' => $unidade->id,
            'cliente_nome' => '',
            'cliente_email' => 'invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['cliente_nome', 'cliente_email']);

        $this->assertDatabaseCount('reserva', 0);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
    }
}
