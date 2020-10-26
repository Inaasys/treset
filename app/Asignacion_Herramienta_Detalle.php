<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Asignacion_Herramienta_Detalle extends Model
{
    protected $table = 'asignacion_herramientas_detalles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_asignacion_herramienta', 
        'asignacion',
        'fecha',
        'herramienta',
        'descripcion',
        'unidad',
        'cantidad',
        'precio',
        'total',
        'estado_herramienta'
    ];

    public function getcantidadAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }
    public function getprecioAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }
    public function gettotalAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }
}
