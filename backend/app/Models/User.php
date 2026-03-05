<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'password',
        'role',
        'ativo',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'ativo'    => 'boolean',
    ];

    // Relacionamentos
    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'usuario_id');
    }

    // Helpers de role
    public function isDiretor(): bool
    {
        return in_array($this->role, ['DIRETOR', 'VICE']);
    }

    public function isOrientadora(): bool
    {
        return $this->role === 'ORIENTADORA';
    }

    public function isAssistente(): bool
    {
        return $this->role === 'ASSISTENTE';
    }
}
