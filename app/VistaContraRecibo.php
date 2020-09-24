<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaContraRecibo extends Model
{
    protected $table = 'VistaContraRecibos';
    protected $fillable = [
        'ContraRecibo',
        'Serie',
        'Folio',
        'Fecha',
        'Proveedor',
        'NombreProveedor',
        'Total',
        'Obs',
        'Status',
        'Periodo',
        'Facturas',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'NumeroProveedor',
        'RfcProveedor',
        'CodigoPostalProveedor',
        'PlazoProveedor',
        'TelefonosProveedor',
        'Email1Proveedor',
    ];
    //Total
    public function getTotalAttribute($value){
        return Helpers::convertirvalorcorrecto($value);
    }
}
