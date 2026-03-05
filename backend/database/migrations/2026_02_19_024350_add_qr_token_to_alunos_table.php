<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Aluno;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->string('qr_token')->unique()->nullable()->after('matricula');
        });

        // Gera QR token único para os alunos já existentes
        Aluno::all()->each(function ($aluno) {
            $aluno->update(['qr_token' => 'ALU_' . strtoupper(Str::random(10))]);
        });
    }

    public function down(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
