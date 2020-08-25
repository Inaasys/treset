<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabla extends Model
{
    public $timestamps = false;
    protected $table = 'Tablas';
    protected $primaryKey = 'Tabla';
    protected $fillable = [
        'Tabla', 
        'Sentencia',
        'Cadena',
        'Busqueda',
        'Permisos',
        'Seguridad',
        'Ordenar',
        'Filtro',
        'Extender',
        'Renglon',
        'Altura',
        'Posicion',
        'Usuario'
    ];
}
