<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'turno', 'ano_letivo', 'qr_token', 'ativa'];

    protected $casts = ['ativa' => 'boolean'];

    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }

    public function frequencias()
    {
        return $this->hasMany(Frequencia::class);
    }
}
