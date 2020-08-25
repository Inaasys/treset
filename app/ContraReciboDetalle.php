<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContraReciboDetalle extends Model
{
    public $timestamps = false;
    protected $table = 'ContraRecibos Detalles';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'ContraRecibo', 
        'Fecha',
        'Proveedor',
        'Compra',
        'Factura',
        'Remision',
        'Plazo',
        'FechaAPagar',
        'Total'
    ]; 
}
