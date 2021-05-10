<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class NotaCliente extends Model
{
    public $timestamps = false;
    protected $table = 'Notas Cliente';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Nota', 
        'Serie',
        'Folio',
        'Esquema',
        'Cliente',
        'Fecha',
        'Almacen',
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
        'TipoRelacion',
        'Confirmacion',
        'FormaPago',
        'MetodoPago',
        'UsoCfdi',
        'ResidenciaFiscal',
        'NumRegIdTrib',
        'EmisorRfc',
        'EmisorNombre',
        'ReceptorRfc',
        'ReceptorNombre',
        'FechaTimbrado',
        'UUID',
        'Hora',
        'Periodo'
    ];
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
    //SubTotal
    public function getSubTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //Iva
    public function getIvaAttribute($value){
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
    //Total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //TipoCambio
    public function getTipoCambioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
