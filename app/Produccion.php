<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Produccion extends Model
{
    public $timestamps = false;
    protected $table = 'Produccion';
    protected $primaryKey = 'Folio';
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
        'Periodo',
        'Total',
        'Cliente',
        'Producido'
    ];
    //cantidad
    public function getCantidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
