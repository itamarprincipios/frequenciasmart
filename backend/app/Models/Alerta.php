<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = ['aluno_id', 'tipo', 'mes_referencia', 'enviado'];

    protected $casts = ['enviado' => 'boolean'];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }
}
