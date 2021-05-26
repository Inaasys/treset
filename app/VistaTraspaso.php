<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaTraspaso extends Model
{
    protected $table = 'VistaTraspasos';
    protected $fillable = [
        'Traspaso',
        'Serie',
        'Folio',
        'Fecha',
        'De',
        'A',
        'Orden',
        'StatusOrden',
        'Nombre',
        'Tipo',
        'Unidad',
        'Economico',
        'Total',
        'Usuario',
        'Equipo',
        'Status',
        'Periodo',
        'Referencia',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Costo',
        'Comision',
        'Utilidad',
        'Obs',
        'MotivoBaja',
        'NombreDe',
        'NombreA',
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
    //total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //subtotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //comision
    public function getComisionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //utilidad
    public function getUtilidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    
}
