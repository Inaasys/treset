<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    public $timestamps = false;
    protected $table = 'Ordenes de Compra';
    protected $primaryKey = 'Folio';
    protected $fillable = [
        'Orden', 
        'Serie',
        'Folio',
        'Proveedor',
        'Fecha',
        'Plazo',
        'Almacen',
        'AutorizadoPor',
        'AutorizadoFecha',
        'Referencia',
        'Tipo',
        'Importe',
        'Descuento',
        'SubTotal',
        'Iva',
        'Total',
        'Obs',
        'Status',
        'MotivoBaja',
        'Equipo',
        'Usuario',
        'Periodo'
    ];
}
