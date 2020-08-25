<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsoCFDI extends Model
{
    protected $table = 'c_UsoCFDI';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Clave',
        'Nombre',
        'Fisica',
        'Moral'
    ];
}
