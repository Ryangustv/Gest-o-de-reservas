<?php

namespace Tests\Unit;

use App\Models\ReservaModel;
use App\ViewModels\ReservaViewModel;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ReservaViewModelTest extends TestCase
{
    public function test_mostra_dados_do_cliente_e_da_unidade(): void
    {
        $cliente = [
            'cliente_nome' => 'Ana Silva',
            'cliente_email' => 'ana@example.com',
            'data_reserva' => '2026-10-18',
        ];
        $reserva = new ReservaModel($cliente);
        $reserva->id = 1;
        $reserva->unidade_numero = '101';
        $reserva->empreendimento_nome = 'Residencial Jardim';

        $viewModel = new ReservaViewModel(new Collection([$reserva]));

        $this->assertSame(['reservas' => [[
            'id' => 1,
            ...$cliente,
            'unidade' => ['numero' => '101', 'empreendimento' => 'Residencial Jardim'],
        ]]], $viewModel->toArray());
    }

    public function test_retorna_lista_vazia_quando_nao_ha_reservas(): void
    {
        $viewModel = new ReservaViewModel(new Collection);

        $this->assertSame(['reservas' => []], $viewModel->toArray());
    }

    public function test_retorna_campos_nulos_quando_nao_ha_unidade(): void
    {
        $reserva = new ReservaModel;
        $viewModel = new ReservaViewModel(new Collection([$reserva]));
        $dados = $viewModel->toArray();

        $this->assertSame(['numero' => null, 'empreendimento' => null], $dados['reservas'][0]['unidade']);
    }
}
