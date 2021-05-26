<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCuentaPorCobrar extends Model
{
    protected $table = 'VistaCuentasPorCobrar';
    protected $fillable = [
        'Pago',
        'Serie',
        'Folio',
        'Corte',
        'Fecha',
        'FechaPago',
        'Cliente',
        'Banco',
        'Esquema',
        'Abono',
        'Anotacion',
        'UUID',
        'Moneda',
        'TipoCambio',
        'EmisorRfc',
        'EmisorNombre',
        'LugarExpedicion',
        'RegimenFiscal',
        'ReceptorRfc',
        'ReceptorNombre',
        'FormaPago',
        'NumOperacion',
        'RfcEmisorCtaOrd',
        'NomBancoOrdExt',
        'CtaOrdenante',
        'RfcEmisorCtaBen',
        'CtaBeneficiario',
        'TipoCadPago',
        'CertPago',
        'CadPago',
        'SelloPago',
        'Hora',
        'TipoRelacion',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo',
        'NumeroCliente',
        'NombreCliente',
        'RfcCliente',
        'NumeroFormaPago',
        'ClaveFormaPago',
        'NombreFormaPago'
    ];
    protected $casts = [
        'Fecha' => 'datetime:Y-m-d',
    ];
    //Abono
    public function getAbonoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
    //TipoCambio
    public function getTipoCambioAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
