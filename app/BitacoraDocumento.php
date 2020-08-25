<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BitacoraDocumento extends Model
{
    public $timestamps = false;
    protected $table = 'Bitacora Documentos';
    //protected $primaryKey = 'Numero';
    protected $fillable = [
        'Docuento', 
        'Movimiento',
        'Aplicacion',
        'Fecha',
        'Status',
        'Usuario',
        'Equipo',
        'Periodo'
    ];
}
