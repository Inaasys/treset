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
        'num_remision',
        'num_equipo',
        'subtotal',
        'iva',
        'total',
        'ot_tecnodiesel',
        'ot_tyt',
        'status',
        'motivo_baja',
        'equipo',
        'usuario',
        'periodo',
        'folio'
    ];


}
