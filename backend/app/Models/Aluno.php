<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Aluno extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'matricula', 'qr_token', 'turma_id', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Aluno $aluno) {
            if (empty($aluno->qr_token)) {
                $aluno->qr_token = 'ALU_' . strtoupper(Str::random(10));
            }
        });
    }

    /** Payload JSON que será codificado no QR Code */
    public function qrPayload(): string
    {
        return json_encode([
            'aluno_id' => $this->id,
            'qr_token' => $this->qr_token,
        ]);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function frequencias()
    {
        return $this->hasMany(Frequencia::class);
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class);
    }
}
