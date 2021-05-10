<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Traspaso extends Model
{
    public $timestamps = false;
    protected $table = 'Traspasos';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Traspaso', 
        'Serie',
        'Folio',
        'Fecha',
        'De',
        'A',
        'Referencia',
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
