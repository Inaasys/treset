<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    public $timestamps = false;
    protected $table = 'c_FormaPago';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre'
    ];
}
