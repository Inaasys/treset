<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    public $timestamps = false;
    protected $table = 'c_Estado';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Pais', 
        'Nombre'
    ];
}
