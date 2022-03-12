<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCartaPorte extends Model
{
    public $timestamps = false;
    protected $table = 'VistaCartasPorte';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'CartaPorte', 
        'Serie',
        'Folio',
        'Fecha',
        'Cliente',
        'Status',
        'Periodo',
        'Esquema',
        'Agente',
        'Tipo',
        'Unidad',
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
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'CondicionesDePago',
        'LugarExpedicion',
        'RegimenFiscal',
        'FormaPago',
        'MetodoPago',
        'UsoCfdi',
        'ResidenciaFiscal',
        'TipoRelacion',
        'NumRegIdTrib',
        'FechaTimbrado',
        'UUID',
        'Hora',
        'TransporteInternacional',
        'TotalDistanciaRecorrida',
        'TotalMercancias',
        'RfcRemitente',
        'NombreRemitente',
        'FechaSalida',
        'CalleRemitente',
        'NoExteriorRemitente',
        'NoInteriorRemitente',
        'ColoniaRemitente',
        'LocalidadRemitente',
        'ReferenciaRemitente',
        'MunicipioRemitente',
        'EstadoRemitente',
        'PaisRemitente',
        'CodigoPostalRemitente',
        'RfcDestinatario',
        'NombreDestinatario',
        'FechaLlegada',
        'DistanciaRecorrida',
        'CalleDestinatario',
        'NoExteriorDestinatario',
        'NoInteriorDestinatario',
        'ColoniaDestinatario',
        'LocalidadDestinatario',
        'ReferenciaDestinatario',
        'MunicipioDestinatario',
        'EstadoDestinatario',
        'PaisDestinatario',
        'CodigoPostalDestinatario',
        'ClaveTransporte',
        'RfcOperador',
        'NombreOperador',
        'NumeroLicencia',
        'CalleOperador',
        'NoExteriorOperador',
        'NoInteriorOperador',
        'ColoniaOperador',
        'LocalidadOperador',
        'ReferenciaOperador',
        'MunicipioOperador',
        'EstadoOperador',
        'PaisOperador',
        'CodigoPostalOperador',
        'PermisoSCT',
        'NumeroPermisoSCT',
        'NombreAsegurado',
        'NumeroPolizaSeguro',
        'ConfiguracionVehicular',
        'PlacaVehiculoMotor',
        'AnoModeloVehiculoMotor',
        'SubTipoRemolque',
        'PlacaRemolque'
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
    //total distancia recorrida
    public function getTotalDistanciaRecorridaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //distancia recorrida
    public function getDistanciaRecorridaAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
