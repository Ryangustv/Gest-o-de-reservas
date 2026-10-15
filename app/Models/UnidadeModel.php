<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadeModel extends Model
{
    protected $table = 'unidade';

    public $timestamps = false;

    protected $fillable = [
        'empreendimento_id',
        'numero',
        'status',
    ];
}
