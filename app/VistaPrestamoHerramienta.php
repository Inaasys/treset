<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaPrestamoHerramienta extends Model
{
    protected $table = 'VistaPrestamoHerramientas';
    protected $fillable = [
        'id',
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
        'periodo',
        'nombre_recibe_herramienta',
        'tipo_recibe_herramienta',
        'nombre_entrega_herramienta',
        'tipo_entrega_herramienta'
    ];
    //total
    public function gettotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
