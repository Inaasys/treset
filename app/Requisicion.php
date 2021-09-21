<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requisicion extends Model
{
    public $timestamps = false;
    protected $table = 'Requisiciones';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Requisicion',
        'Serie',
        'Folio',
        'Fecha',
        'Orden',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
        'Costo',
        'Comision',
        'Utilidad',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
