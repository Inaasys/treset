<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCotizacionProducto extends Model
{
    protected $table = 'VistaCotizacionesProductos';
    protected $fillable = [
        'Cotizacion',
        'Serie',
        'Folio',
        'Cliente',
        'NombreCliente',
        'Agente',
        'Plazo',
        'Fecha',
        'Tipo',
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
}
