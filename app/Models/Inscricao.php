<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscricao extends Model
{
    public $timestamps = false;
    public $primaryKey = 'pid';

    use HasFactory;

    protected $table = 'userdata';
    protected $fillable = [
        'pid',
        'sid',
        'uid',
        'gid',
        'presence',
        'pack_id',
        'minicurso',
        'viagem',
        'kit',
        'camiseta',
        'alojamento',
        'status'
    ];

    protected $casts = [
        'presence' => 'array',
        'alojamento' => 'boolean',
    ];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class, 'pack_id', 'id');
    }
}
