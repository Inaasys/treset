<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    public $timestamps = false;
    protected $table = 'c_Municipio';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Estado', 
        'Nombre'
    ];
}
