<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Asignacion_Herramienta extends Model
{
    protected $table = 'asignacion_herramientas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'asginacion', 
        'serie',
        'fecha',
        'recibe_herramienta',
        'entrega_herramienta',
        'total',
        'observaciones',
        'autorizado_por',
        'fecha_autorizacion',
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
