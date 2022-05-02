<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_ObjetoImp extends Model
{
    public $timestamps = false;
    protected $table = 'c_ObjetoImp';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Descripcion',
        'FechaDeInicioDeVigencia',
        'FechaDeFinDeVigencia'
    ];
}
