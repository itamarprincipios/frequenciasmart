<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Turma;
use App\Models\Aluno;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === USUÁRIOS DE TESTE ===
        $diretor = User::create([
            'nome'     => 'Carlos Diretor',
            'email'    => 'diretor@edutrack.com',
            'password' => Hash::make('senha123'),
            'role'     => 'DIRETOR',
            'ativo'    => true,
        ]);

        User::create([
            'nome'     => 'Ana Vice-Diretora',
            'email'    => 'vice@edutrack.com',
            'password' => Hash::make('senha123'),
            'role'     => 'VICE',
            'ativo'    => true,
        ]);

        User::create([
            'nome'     => 'Maria Orientadora',
            'email'    => 'orientadora@edutrack.com',
            'password' => Hash::make('senha123'),
            'role'     => 'ORIENTADORA',
            'ativo'    => true,
        ]);

        $assistente = User::create([
            'nome'     => 'João Assistente',
            'email'    => 'assistente@edutrack.com',
            'password' => Hash::make('senha123'),
            'role'     => 'ASSISTENTE',
            'ativo'    => true,
        ]);

        // === TURMAS DE TESTE ===
        $turma1 = Turma::create([
            'nome'       => '5º Ano A',
            'turno'      => 'MANHA',
            'ano_letivo' => 2026,
            'qr_token'   => 'TURMA_1_2026_' . bin2hex(random_bytes(4)),
            'ativa'      => true,
        ]);

        $turma2 = Turma::create([
            'nome'       => '6º Ano B',
            'turno'      => 'TARDE',
            'ano_letivo' => 2026,
            'qr_token'   => 'TURMA_2_2026_' . bin2hex(random_bytes(4)),
            'ativa'      => true,
        ]);

        // === ALUNOS DE TESTE ===
        $alunos1 = [
            ['nome' => 'Pedro Alves',    'matricula' => '2026001'],
            ['nome' => 'Lucia Ferreira', 'matricula' => '2026002'],
            ['nome' => 'Bruno Castro',   'matricula' => '2026003'],
            ['nome' => 'Camila Santos',  'matricula' => '2026004'],
            ['nome' => 'Marcos Lima',    'matricula' => '2026005'],
        ];

        foreach ($alunos1 as $a) {
            Aluno::create(array_merge($a, ['turma_id' => $turma1->id, 'ativo' => true]));
        }

        $alunos2 = [
            ['nome' => 'Fernanda Costa', 'matricula' => '2026006'],
            ['nome' => 'Rafael Melo',    'matricula' => '2026007'],
            ['nome' => 'Juliana Souza',  'matricula' => '2026008'],
        ];

        foreach ($alunos2 as $a) {
            Aluno::create(array_merge($a, ['turma_id' => $turma2->id, 'ativo' => true]));
        }
    }
}
