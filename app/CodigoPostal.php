<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    public $timestamps = false;
    protected $table = 'c_CodigoPostal';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave', 
        'Estado',
        'Municipio',
        'Localidad'
    ];
}
