<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operador extends Model
{
    protected $table = 'operadores';
    protected $primaryKey = 'id';
    protected $fillable = [ 
        'Rfc',
        'Nombre',
        'NumeroLicencia',
        'Calle',
        'NoExterior',
        'NoInterior',
        'Colonia',
        'Localidad',
        'Referencia',
        'Municipio',
        'Estado',
        'Pais',
        'CodigoPostal',
    ];
}
