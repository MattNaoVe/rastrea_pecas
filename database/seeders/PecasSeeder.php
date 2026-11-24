<?php

namespace Database\Seeders;

use App\Models\Local;
use App\Models\Movimentacao;
use App\Models\Peca;
use Illuminate\Database\Seeder;

class PecasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Biblioteca',          'Acervo e sala de estudos'],
            ['Auditório',           'Eventos e apresentações'],
            ['Centro de Usinagem',  'Fabricação e usinagem de peças'],
        ] as [$nome, $descricao]) {
            Local::firstOrCreate(['nome' => $nome], ['descricao' => $descricao]);
        }

        $pecas = [
            ['001', 'L2026-01', '2026-10-01', 'Biblioteca',         '08:30', 'Em estoque',  null],
            ['002', 'L2026-01', '2026-10-01', 'Centro de Usinagem', '09:15', 'Em produção', 'Biblioteca'],
            ['003', 'L2026-02', '2026-10-15', 'Auditório',          '10:40', 'Em uso',      'Biblioteca'],
            ['004', 'L2026-02', '2026-10-15', 'Biblioteca',         '14:05', 'Em estoque',  'Centro de Usinagem'],
            ['B04', 'LT2025',   '2026-09-15', 'Centro de Usinagem', '08:30', 'Em Produção', null],
        ];

        foreach ($pecas as [$id, $lote, $data, $local, $hora, $status, $origem]) {
            Peca::updateOrCreate(['id' => $id], [
                'lote' => $lote, 'data_fabricacao' => $data, 'local' => $local,
                'horario' => $hora, 'status' => $status,
            ]);

            if (!Movimentacao::where('peca_id', $id)->exists()) {
                Movimentacao::create([
                    'peca_id' => $id, 'local_origem' => $origem, 'local_destino' => $local,
                    'status' => $status, 'registrado_em' => now(),
                ]);
            }
        }
    }
}
