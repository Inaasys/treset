<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TituloFirma extends Model
{

    public $timestamps = false;
    protected $table = 'titulos_firmas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id', 
        'Titulo'
    ];
}
