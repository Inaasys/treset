<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Factura extends Model
{
    public $timestamps = false;
    protected $table = 'Facturas';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Factura', 
        'Serie',
        'Folio',
        'Esquema',
        'Cliente',
        'Agente',
        'Fecha',
        'Plazo',
        'Depto',
        'Orden',
        'Pedido',
        'Tipo',
        'Unidad',
        'Lpa',
        'BloquearObsoleto',
        'Incobrable',
        'TipoPA',
        'Refactura',
        'Importe',
        'Descuento',
        'Ieps',
        'SubTotal',
        'Iva',
        'IvaRetencion',
        'IsrRetencion',
        'IepsRetencion',
        'ImpLocRetenciones',
        'ImpLocTraslados',
        'Total',
        'Costo',
        'Comision',
        'Utilidad',
        'Abonos',
        'Descuentos',
        'Saldo',
        'Moneda',
        'TipoCambio',
        'Descripcion',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
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
        'UUID',
        'Hora',
        'Periodo',
        'Periodicidad',
        'Meses'
    ];
    //importe
    public function getImporteAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //descuento
    public function getDescuentoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ieps
    public function getIepsAttribute($value){
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
    //iva retencion
    public function getIvaRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //isr retencion
    public function getIsrRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //ieps retencion
    public function getIepsRetencionAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //iimpuestos locales retenciones
    public function getImpLocRetencionesAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //impuesto locales traslados
    public function getImpLocTrasladosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //total
    public function getTotalAttribute($value){
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
    //abonos
    public function getAbonosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //descuentos
    public function getDescuentosAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //saldo
    public function getSaldoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //tipo cambio
    public function getTipoCambioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
