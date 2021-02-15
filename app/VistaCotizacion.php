<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCotizacion extends Model
{
    protected $table = 'VistaCotizaciones';
    protected $fillable = [
        'id',
        'cotizacion',
        'serie',
        'fecha',
        'num_equipo',
        'subtotal',
        'iva',
        'total',
        'ot_tecnodiesel',
        'ot_tyt',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo',
        'num_remision'
    ];

    //SubTotal
    public function getsubtotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Iva
    public function getivaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Total
    public function gettotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
