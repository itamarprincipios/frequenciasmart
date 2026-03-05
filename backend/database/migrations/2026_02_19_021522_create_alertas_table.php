<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->enum('tipo', ['CONSECUTIVA', 'INTERCALADA']);
            $table->string('mes_referencia'); // formato: "2026-02"
            $table->boolean('enviado')->default(false);
            $table->timestamps();

            // Impede alertas duplicados do mesmo tipo no mesmo mês
            $table->unique(['aluno_id', 'tipo', 'mes_referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
