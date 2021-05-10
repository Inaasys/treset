<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaXCobrar extends Model
{
    public $timestamps = false;
    protected $table = 'CxC';
    protected $primaryKey = 'Folio';
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
        'TipoCadPgo',
        'CertPago',
        'CadPago',
        'SelloPago',
        'Hora',
        'TipoRelacion',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
