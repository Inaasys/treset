<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_TipoRelacion extends Model
{
    public $timestamps = false;
    protected $table = 'c_TipoRelacion';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre'
    ];
}
