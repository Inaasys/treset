<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    public $timestamps = false;
    protected $table = 'c_MetodoPago';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre'
    ];
}
