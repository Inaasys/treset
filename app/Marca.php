<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    public $timestamps = false;
    protected $table = 'Marcas';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Utilidad1',
        'Utilidad2',
        'Utilidad3',
        'Utilidad4',
        'Utilidad5',
        'Status'
    ];
}
