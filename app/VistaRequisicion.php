<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaRequisicion extends Model
{
    protected $table = 'VistaRequisiciones';
    protected $fillable = [
        'Requisicion',
        'Serie',
        'Folio',
        'Nombre',
        'Total',
        'Orden',
        'Status',
        'MotivoBaja',
        'Usuario',
        'Equipo',
        'Periodo',
        'Fecha',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Costo',
        'Comision',
        'Utilidad',
        'Obs',
        'Tipo',
        'Unidad',
        'Cliente',
        'Vin',
        'Economico'
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
    //total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //SubTotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Costo
    public function getCostoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Comision
    public function getComisionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Utilidad
    public function getUtilidadAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
