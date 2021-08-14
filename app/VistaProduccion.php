<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaProduccion extends Model
{
    protected $table = 'VistaProduccion';
    protected $fillable = [
        'Produccion', 
        'Serie', 
        'Folio', 
        'Fecha', 
        'Codigo', 
        'Almacen', 
        'Cantidad', 
        'Costo', 
        'Obs', 
        'Status', 
        'Motivo de Baja', 
        'Equipo', 
        'Usuario', 
        'Periodo'
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
    //cantidad
    public function getCantidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
