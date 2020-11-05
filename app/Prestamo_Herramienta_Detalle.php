<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Prestamo_Herramienta_Detalle extends Model
{
    protected $table = 'prestamo_herramientas_detalles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_prestamo_herramienta', 
        'id_detalle_asignacion_herramienta',
        'prestamo',
        'fecha',
        'herramienta',
        'descripcion',
        'unidad',
        'cantidad',
        'precio',
        'total',
        'estado_herramienta',
        'duracion',
        'termino_prestamo',
        'item'
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
