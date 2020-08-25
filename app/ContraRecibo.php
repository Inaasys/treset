<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContraRecibo extends Model
{
    public $timestamps = false;
    protected $table = 'ContraRecibos';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'ContraRecibo',
        'Serie',
        'Folio',
        'Fecha',
        'Proveedor',
        'Facturas',
        'Total',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
