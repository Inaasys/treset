<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClaveProdServ extends Model
{
    public $timestamps = false;
    protected $table = 'c_ClaveProdServ';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero',
        'Clave', 
        'Nombre',
        'Usual'
    ];
}
