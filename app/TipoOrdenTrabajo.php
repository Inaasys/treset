<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoOrdenTrabajo extends Model
{
    public $timestamps = false;
    protected $table = 'TiposOrdenTrabajo';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
