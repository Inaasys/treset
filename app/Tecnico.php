<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class Tecnico extends Model
{
    public $timestamps = false;
    protected $table = 'Tecnicos';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Area',
        'Objetivo',
        'Planeacion',
        'Status'
    ];

    public function getObjetivoAttribute($value)
    {
        return Helpers::convertirvalorcorrecto($value);
    }

}
