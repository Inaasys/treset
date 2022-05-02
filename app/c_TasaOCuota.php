<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_TasaOCuota extends Model
{
    public $timestamps = false;
    protected $table = 'c_TasaOCuota';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Tipo', 
        'ValorMinimo',
        'ValorMaximo',
        'Impuesto',
        'Factor',
        'Traslado',
        'Retencion'
    ];
}
