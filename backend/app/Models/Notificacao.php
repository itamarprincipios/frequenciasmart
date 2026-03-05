<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notificacao extends Model
{
    use HasFactory;

    protected $fillable = ['usuario_id', 'titulo', 'mensagem', 'lida'];

    protected $casts = ['lida' => 'boolean'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
