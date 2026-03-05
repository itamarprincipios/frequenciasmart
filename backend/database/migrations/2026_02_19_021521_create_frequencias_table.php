<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->date('data');
            $table->enum('status', ['PRESENTE', 'FALTA'])->default('PRESENTE');
            $table->foreignId('registrado_por')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Impede duplicidade de frequência por aluno no mesmo dia
            $table->unique(['aluno_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequencias');
    }
};
