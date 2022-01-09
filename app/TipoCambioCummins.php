<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoCambioCummins extends Model
{
    public $timestamps = false;
    protected $table = 'tipo_cambio_cummins';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Moneda',
        'Fecha',
        'Valor'
    ];
}
