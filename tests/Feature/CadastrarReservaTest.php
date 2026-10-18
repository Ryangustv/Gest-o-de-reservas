<?php

namespace Tests\Feature;

use App\Models\EmpreendimentoModel;
use App\Models\ReservaModel;
use App\Models\UnidadeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_reserva_is_created_and_unidade_becomes_reserved(): void
    {
        $unidade = $this->unidade();
        $dados = $this->dados($unidade);

        $this->postJson('/reservas', $dados)->assertCreated()->assertJson(['success' => true]);

        $this->assertDatabaseHas('reserva', [...$dados, 'data_reserva' => now()->toDateString()]);
        $this->assertDatabaseHas('unidade', ['id' => $unidade->id, 'status' => 'reservado']);
    }

    public function test_reserved_unidade_cannot_be_reserved_again(): void
    {
        $unidade = $this->unidade('reservado');

        $this->postJson('/reservas', $this->dados($unidade))->assertUnprocessable()->assertJsonValidationErrors('unidade_id');

        $this->assertDatabaseCount('reserva', 0);
    }

    public function test_existing_reserva_prevents_a_second_reserva_even_with_inconsistent_status(): void
    {
        $unidade = $this->unidade();
        ReservaModel::create([...$this->dados($unidade), 'data_reserva' => now()->toDateString()]);

        $this->postJson('/reservas', $this->dados($unidade))->assertUnprocessable()->assertJsonValidationErrors('unidade_id');

        $this->assertDatabaseCount('reserva', 1);
    }

    public function test_invalid_client_data_does_not_reserve_unidade(): void
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
