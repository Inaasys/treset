<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    public $timestamps = false;
    protected $table = 'Departamentos';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
