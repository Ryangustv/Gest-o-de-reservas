<?php

namespace Tests\Feature;

use App\Models\EmpreendimentoModel;
use App\Models\ReservaModel;
use App\Models\UnidadeModel;
use App\Repositories\Manager\ReservaRepository;
use App\ViewModels\ReservaViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\EmpreendimentoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        $this->postJson('/reservas', $dados)->assertCreated()->assertJson(['success' => true]);

        $this->assertDatabaseHas('reserva', [...$dados, 'data_reserva' => now()->toDateString()]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
        $this->assertFalse(Cache::store('redis')->has(EmpreendimentoService::CACHE_KEY));
    }

    public function test_cancelamento_limpa_cache_e_libera_unidade(): void
    {
        $unidade = $this->unidade('reservado');
        $reserva = ReservaModel::create([...$this->dados($unidade), 'data_reserva' => now()->toDateString()]);
        Cache::store('redis')->put(EmpreendimentoService::CACHE_KEY, ['dados anteriores'], 300);

        $this->deleteJson('/reservas/'.$reserva->id)->assertOk();

        $this->assertDatabaseMissing('reserva', ['id' => $reserva->id]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'disponivel']);
        $this->assertFalse(Cache::store('redis')->has(EmpreendimentoService::CACHE_KEY));
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

        $this->postJson('/reservas', $this->dados($unidade))->assertUnprocessable()->assertJsonValidationErrors('unidade_id');

        $this->assertDatabaseCount('reserva', 1);
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
