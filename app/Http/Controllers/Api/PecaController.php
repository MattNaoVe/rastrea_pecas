<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Movimentacao;
use App\Models\Peca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PecaController extends Controller
{
    /** GET /api/pecas/{id}: dados da peça + histórico (do mais antigo ao mais novo). */
    public function show(string $id): JsonResponse
    {
        $peca = Peca::find($id);

        if (!$peca) {
            return response()->json(['erro' => 'Peça não encontrada.'], 404);
        }

        return response()->json($this->formatar($peca));
    }

    /** PUT /api/pecas/{id}: muda o local da peça e grava a movimentação. */
    public function update(Request $request, string $id): JsonResponse
    {
        $peca = Peca::find($id);

        if (!$peca) {
            return response()->json(['erro' => 'Peça não encontrada.'], 404);
        }

        $entrada = trim((string) $request->input('local', ''));

        if ($entrada === '') {
            return response()->json(['erro' => 'Informe o novo local.'], 422);
        }

        $local = $this->localValido($entrada);

        if (!$local) {
            $nomes = Local::orderBy('nome')->pluck('nome')->implode(', ');
            return response()->json(['erro' => "Local inválido. Locais disponíveis: {$nomes}."], 422);
        }

        if ($local === $peca->local) {
            return response()->json(['erro' => "A peça já está em {$local}."], 422);
        }

        DB::transaction(function () use ($peca, $local) {
            $origem = $peca->local;

            $peca->update([
                'local'   => $local,
                'horario' => now()->format('H:i'),
            ]);

            Movimentacao::create([
                'peca_id'       => $peca->id,
                'local_origem'  => $origem,
                'local_destino' => $local,
                'status'        => $peca->status,
                'registrado_em' => now(),
            ]);
        });

        return response()->json(['dados' => $this->formatar($peca->fresh())]);
    }

    /** GET /api/locais: nomes dos locais, para preencher as listas do front. */
    public function locais(): JsonResponse
    {
        return response()->json(Local::orderBy('nome')->pluck('nome'));
    }

    /** POST /api/pecas: cadastra uma peça e registra a primeira movimentação. */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['id' => strtoupper(trim((string) $request->input('id', '')))]);

        $validador = Validator::make($request->all(), [
            'id'              => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', 'unique:pecas,id'],
            'lote'            => ['required', 'string', 'max:100'],
            'data_fabricacao' => ['required', 'date_format:Y-m-d'],
            'horario'         => ['required', 'date_format:H:i'],
            'local'           => ['required', 'exists:locais,nome'],
            'status'          => ['required', 'string', 'max:100'],
        ], [
            'id.required'                 => 'Informe o código da peça.',
            'id.regex'                    => 'O código deve ter apenas letras, números, hífen ou sublinhado.',
            'id.unique'                   => 'Já existe uma peça com esse código.',
            'lote.required'               => 'Informe o lote.',
            'data_fabricacao.required'    => 'Informe a data de fabricação.',
            'data_fabricacao.date_format' => 'Data de fabricação inválida.',
            'horario.required'            => 'Informe o horário.',
            'horario.date_format'         => 'Horário inválido.',
            'local.required'              => 'Selecione o local.',
            'local.exists'                => 'Local inválido.',
            'status.required'             => 'Informe o status.',
        ]);

        if ($validador->fails()) {
            return response()->json(['erro' => $validador->errors()->first()], 422);
        }

        $dados = $validador->validated();

        DB::transaction(function () use ($dados) {
            Peca::create($dados);

            Movimentacao::create([
                'peca_id'       => $dados['id'],
                'local_origem'  => null,
                'local_destino' => $dados['local'],
                'status'        => $dados['status'],
                'registrado_em' => now(),
            ]);
        });

        return response()->json(['dados' => $this->formatar(Peca::find($dados['id']))], 201);
    }

    /** Aceita o local sem diferenciar maiúsculas e acentos ("auditorio" = "Auditório"). */
    private function localValido(string $entrada): ?string
    {
        $chave = Str::lower(Str::ascii($entrada));

        foreach (Local::all() as $local) {
            if (Str::lower(Str::ascii($local->nome)) === $chave) {
                return $local->nome;
            }
        }

        return null;
    }

    /** Formato que o script.js do front espera. */
    private function formatar(Peca $peca): array
    {
        $historico = $peca->movimentacoes()
            ->orderBy('registrado_em')
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'data'    => $m->registrado_em->format('d/m/Y'),
                'horario' => $m->registrado_em->format('H:i'),
                'local'   => $m->local_destino,
            ])
            ->all();

        return [
            'id'             => $peca->id,
            'lote'           => $peca->lote,
            'dataFabricacao' => $peca->data_fabricacao->format('d/m/Y'),
            'local'          => $peca->local,
            'horario'        => substr((string) $peca->horario, 0, 5),
            'status'         => $peca->status,
            'historico'      => $historico,
        ];
    }
}
