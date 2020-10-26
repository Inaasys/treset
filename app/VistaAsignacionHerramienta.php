<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaAsignacionHerramienta extends Model
{
    protected $table = 'VistaAsignacionHerramientas';
    protected $fillable = [
        'id',
        'asignacion',
        'serie',
        'fecha',
        'recibe_herramienta',
        'entrega_herramienta',
        'total',
        'observaciones',
        'autorizado_por',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo',
        'fecha_autorizacion ',
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
