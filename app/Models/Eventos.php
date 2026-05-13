<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'eid';
    public $timestamps = false;
    protected $fillable = [
        'sid',
        'type',
        'start',
        'end',
        'name',
        'info',
        'slots',
        'extra'
    ];
}
