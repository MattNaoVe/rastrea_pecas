<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peca extends Model
{
    protected $table = 'pecas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'lote', 'data_fabricacao', 'local', 'horario', 'status'];

    protected $casts = [
        'data_fabricacao' => 'date:Y-m-d',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class, 'peca_id');
    }
}
