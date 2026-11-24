<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pecas', function (Blueprint $table) {
            $table->string('id')->primary();   // código da peça = conteúdo do QR code
            $table->string('lote');
            $table->date('data_fabricacao');
            $table->string('local');           // local atual
            $table->time('horario');
            $table->string('status');
            $table->timestamps();

            $table->index('lote');
            $table->index('local');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pecas');
    }
};
