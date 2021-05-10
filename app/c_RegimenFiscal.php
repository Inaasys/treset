<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class c_RegimenFiscal extends Model
{
    public $timestamps = false;
    protected $table = 'c_RegimenFiscal';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre',
        'Fisica',
        'Moral'
    ];
}
