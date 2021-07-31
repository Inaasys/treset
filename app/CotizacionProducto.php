<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CotizacionProducto extends Model
{
    public $timestamps = false;
    protected $table = 'Cotizaciones';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Cotizacion', 
        'Serie',
        'Folio',
        'Tipo',
        'Cliente',
        'Fecha',
        'Hora',
        'Plazo',
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
