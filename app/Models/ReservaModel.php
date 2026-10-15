<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UnidadeModel;

class ReservaModel extends Model
{
    protected $table = 'reserva';

    public $timestamps = false;

    protected $fillable = [
        'unidade_id',
        'cliente_nome',
        'cliente_email',
        'data_reserva',
    ];

    public function unidade()
    {
        return $this->belongsTo(UnidadeModel::class, 'unidade_id');
    }
}
