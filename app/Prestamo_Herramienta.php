<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Prestamo_Herramienta extends Model
{
    protected $table = 'prestamo_herramientas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'prestamo', 
        'serie',
        'fecha',
        'recibe_herramienta',
        'entrega_herramienta',
        'total',
        'observaciones',
        'correo',
        'correo_enviado',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo'
    ];

    public function gettotalAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }
}
