<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCompra extends Model
{
    protected $table = 'VistaCompras';
    protected $fillable = [
        'Compra',
        'Proveedor',
        'NombreProveedor',
        'Plazo',
        'Fecha',
        'FechaEmitida',
        'Remision',
        'Factura',
        'Tipo',
        'Almacen',
        'Movimiento',
        'UUID',
        'Orden',
        'SubTotal',
        'Iva',
        'Total',
        'Abonos',
        'Descuentos ',
        'Saldo',
        'TipoCambio',
        'Obs',
        'Equipo',
        'Usuario',
        'Status',
        'Periodo',
        'Folio',
        'Serie',
        'MotivoBaja',
        'ReceptorNombre',
        'ReceptorRfc',
        'EmisorNombre',
        'EmisorRfc',
        'FechaTimbrado',
        'Moneda',
        'ImpLocTraslados',
        'ImpLocRetenciones',
        'IepsRetencion',
        'IsrRetencion',
        'IvaRetencion',
        'Ieps',
        'Descuento',
        'Importe',
        'BloquearObsoleto',
        'Departamento',
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
    //Abonos
    public function getAbonosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Descuentos
    public function getDescuentosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Saldo
    public function getSaldoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //TipoCambio
    public function getTipoCambioAttribute($value){
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
}
