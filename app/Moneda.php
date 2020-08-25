<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    public $timestamps = false;
    protected $table = 'c_Moneda';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre',
        'Decimales'
    ];
}
