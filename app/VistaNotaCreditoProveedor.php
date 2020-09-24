<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaNotaCreditoProveedor extends Model
{
    protected $table = 'VistaNotasCreditoProveedores';
    protected $fillable = [
        'Nota',
        'Proveedor',
        'NombreProveedor',
        'NotaProveedor',
        'Fecha',
        'Almacen',
        'UUID',
        'SubTotal',
        'Iva',
        'Total',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'Folio',
        'Serie',
        'Ieps',
        'Descuento',
        'Importe',
        'ImpLocTraslados',
        'ImpLocRetenciones',
        'IepsRetencion',
        'IsrRetencion',
        'IvaRetencion',
        'Moneda',
        'TipoCambio',
        'FechaEmitida',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'NumeroProveedor',
        'RfcProveedor',
        'CodigoPostalProveedor',
        'PlazoProveedor',
        'TelefonosProveedor',
        'Email1Proveedor'
    ];
    //SubTotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Iva
    public function getIvaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ImpLocTraslados
    public function getImpLocTrasladosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ImpLocRetenciones
    public function getImpLocRetencionesAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IepsRetencion
    public function getIepsRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IsrRetencion
    public function getIsrRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IvaRetencion
    public function getIvaRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Ieps
    public function getIepsAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //TipoCambio
    public function getTipoCambioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
