<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UnidadeModel;

class EmpreendimentoModel extends Model
{
    protected $table = 'empreendimentos';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'cidade',
    ];

    public function unidades()
    {
        return $this->hasMany(UnidadeModel::class, 'empreendimento_id');
    }
}
