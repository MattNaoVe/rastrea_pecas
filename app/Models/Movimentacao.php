<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    protected $table = 'movimentacoes';

    protected $fillable = ['peca_id', 'local_origem', 'local_destino', 'status', 'registrado_em'];

    protected $casts = [
        'registrado_em' => 'datetime',
    ];
}
