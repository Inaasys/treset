<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    public $timestamps = false;
    protected $table = 'Lineas';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
