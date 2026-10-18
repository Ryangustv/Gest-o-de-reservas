<?php

namespace Database\Seeders;

use App\Models\EmpreendimentoModel;
use App\Models\UnidadeModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $exemplos = [
            ['nome' => 'Residencial Jardim', 'cidade' => 'Cascavel', 'unidades' => ['101', '102', '103']],
            ['nome' => 'Residencial Parque', 'cidade' => 'Curitiba', 'unidades' => ['201', '202', '203']],
        ];

        foreach ($exemplos as $exemplo) {
            $empreendimento = EmpreendimentoModel::firstOrCreate([
                'nome' => $exemplo['nome'],
                'cidade' => $exemplo['cidade'],
            ]);

            foreach ($exemplo['unidades'] as $numero) {
                UnidadeModel::firstOrCreate([
                    'empreendimento_id' => $empreendimento->id,
                    'numero' => $numero,
                ], ['status' => 'disponivel']);
            }
        }
    }
}
