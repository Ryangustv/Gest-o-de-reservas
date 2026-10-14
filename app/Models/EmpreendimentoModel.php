<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpreendimentoModel extends Model
{
    protected $table = 'empreendimentos';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'cidade',
    ];
}
