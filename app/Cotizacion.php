<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    public $timestamps = false;
    protected $table = 'cotizaciones_t';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cotizacion', 
        'serie',
        'fecha',
        'subtotal',
        'iva',
        'total',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo',
        'folio'
    ];


}
