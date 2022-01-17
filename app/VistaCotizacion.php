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
        'subtotal',
        'iva',
        'total',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo',
    ];
    protected $casts = [
        'fecha' => 'datetime:Y-m-d',
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
