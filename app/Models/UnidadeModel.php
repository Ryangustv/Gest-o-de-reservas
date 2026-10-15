<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EmpreendimentoModel;

class UnidadeModel extends Model
{
    protected $table = 'unidade';

    public $timestamps = false;

    protected $fillable = [
        'empreendimento_id',
        'numero',
        'status',
    ];

    public function empreendimento()
    {
        return $this->belongsTo(EmpreendimentoModel::class, 'empreendimento_id');
    }
}
