<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaCuentaPorPagar extends Model
{
    protected $table = 'VistaCuentasPorPagar';
    protected $fillable = [
        'Pago',
        'Fecha',
        'Proveedor',
        'NombreProveedor',
        'NombreBanco',
        'Transferencia',
        'Abono',
        'Status',
        'MotivoBaja',
        'Periodo',
        'Folio',
        'Serie',
        'Banco',
        'Cheque',
        'Beneficiario',
        'CuentaDeposito',
        'Anotacion',
        'Equipo',
        'Usuario',
        'NumeroBanco',
        'CuentaBanco',
        'NumeroProveedor',
        'RfcProveedor',
        'CodigoPostalProveedor',
        'PlazoProveedor',
        'TelefonosProveedor',
        'Email1Proveedor'
    ];
    //Abono
    public function getAbonoAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
