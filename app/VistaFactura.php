<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaFactura extends Model
{
    protected $table = 'VistaFacturas';
    protected $fillable = [
        'Factura',
        'Serie',
        'Folio',
        'Fecha',
        'Status',
        'UUID',
        'Esquema',
        'Depto',
        'Cliente',
        'NombreCliente',
        'RfcCliente',
        'Remision',
        'OrdenTrabajo',
        'EconomicoOrdenTrabajo',
        'Pedido',
        'Agente',
        'NombreAgente',
        'Tipo',
        'Plazo',
        'SubTotal',
        'Iva',
        'Total',
        'Abonos',
        'Descuentos',
        'Saldo',
        'Equipo',
        'Usuario',
        'MotivoBaja',
        'Periodo',
        'Orden',
        'Unidad',
        'Lpa',
        'BloquearObsoleto',
        'Incobrable',
        'TipoPA',
        'Refactura',
        'Importe',
        'Descuento',
        'Ieps',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'ImpLocRetenciones',
        'ImpLocTraslados',
        'Costo',
        'Comision',
        'Utilidad',
        'Moneda',
        'TipoCambio',
        'Descripcion',
        'Obs',
        'CondicionesDePago',
        'LugarExpedicion',
        'RegimenFiscal',
        'Confirmacion',
        'FormaPago',
        'MetodoPago',
        'UsoCfdi',
        'ResidenciaFiscal',
        'TipoRelacion',
        'NumRegIdTrib',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'FechaTimbrado',
        'Hora'
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
    public function getDescuentoslAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Saldo
    public function getSaldoAttribute($value){
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
    //Ieps
    public function getIepsAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IvaRetencion
    public function getIvaRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IsrRetencion
    public function getIsrRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //IepsRetencion
    public function getIepsRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ImpLocRetenciones
    public function getImpLocRetencionesAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ImpLocTraslados
    public function getImpLocTrasladosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //TipoCambio
    public function getTipoCambioAttribute($value){
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
