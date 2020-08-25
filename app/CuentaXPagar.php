<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaXPagar extends Model
{
    public $timestamps = false;
    protected $table = 'CxP';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Pago', 
        'Serie',
        'Folio',
        'Fecha',
        'Proveedor',
        'Banco',
        'Cheque',
        'Transferencia',
        'Beneficiario',
        'Abono',
        'CuentaDeposito',
        'Anotacion',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
