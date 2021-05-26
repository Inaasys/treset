<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaOrdenCompra extends Model
{
    protected $table = 'VistaOrdenesDeCompra';
    protected $fillable = [
        'Orden',
        'Proveedor',
        'NombreProveedor',
        'Fecha',
        'AutorizadoPor',
        'AutorizadoFecha',
        'Tipo',
        'Almacen',
        'SubTotal',
        'Iva',
        'Total',
        'Status',
        'Equipo',
        'Usuario',
        'Periodo ',
        'Folio',
        'Serie',
        'Plazo',
        'Referencia',
        'Importe',
        'Descuento',
        'Obs',
        'MotivoBaja',
        'NumeroProveedor',
        'RfcProveedor',
        'CodigoPostalProveedor',
        'PlazoProveedor',
        'TelefonosProveedor',
        'Email1Proveedor',
        'NumeroAlmacen',
        'NombreAlmacen'
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
    //descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    /*
    //fecha
    public function setFechaAttribute($value){
        $this->attributes['first_name'] = strtolower($value);
    }*/
}
