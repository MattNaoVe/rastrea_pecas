<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->string('peca_id');
            $table->string('local_origem')->nullable();
            $table->string('local_destino');
            $table->string('status');
            $table->dateTime('registrado_em')->useCurrent();
            $table->timestamps();

            $table->foreign('peca_id')->references('id')->on('pecas')->cascadeOnDelete();
            $table->index(['peca_id', 'registrado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
