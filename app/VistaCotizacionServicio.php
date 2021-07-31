<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCotizacionServicio extends Model
{
    protected $table = 'VistaCotizacionesServicios';
    protected $fillable = [
        'Cotizacion',
        'Serie',
        'Folio',
        'Cliente',
        'NombreCliente',
        'Agente',
        'Plazo',
        'Fecha',
        'Unidad',
        'Vin',
        'Economico',
        'Marca',
        'Modelo',
        'Año',
        'Kilometros',
        'Placas',
        'Color',
        'SubTotal',
        'Iva',
        'Total',
        'Status',
        'Equipo',
        'Usuario',
        'Periodo '
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
    //subtotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Kilometros
    public function getKilometrosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
