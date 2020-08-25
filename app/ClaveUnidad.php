<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClaveUnidad extends Model
{
    public $timestamps = false;
    protected $table = 'c_ClaveUnidad';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Nombre',
        'Descripcion',
        'Usual'
    ];
}
