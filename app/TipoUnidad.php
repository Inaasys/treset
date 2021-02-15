<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoUnidad extends Model
{
    public $timestamps = false;
    protected $table = 'TiposUnidades';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
