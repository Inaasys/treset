<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    public $timestamps = false;
    protected $table = 'c_Pais';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave', 
        'Nombre'
    ];
}
