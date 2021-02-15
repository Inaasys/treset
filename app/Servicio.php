<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    public $timestamps = false;
    protected $table = 'Servicios';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Codigo', 
        'Servicio', 
        'Unidad',
        'Familia',
        'Costo',
        'Venta',
        'Cantidad',
        'ClaveProducto',
        'ClaveUnidad',
        'Status'
    ];

}
