<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frequencia extends Model
{
    use HasFactory;

    protected $fillable = ['aluno_id', 'turma_id', 'data', 'status', 'registrado_por'];

    protected $casts = ['data' => 'date'];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
