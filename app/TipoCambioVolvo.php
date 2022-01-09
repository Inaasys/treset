<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoCambioVolvo extends Model
{
    public $timestamps = false;
    protected $table = 'tipo_cambio_volvo';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Moneda',
        'Fecha',
        'Valor'
    ];
}
