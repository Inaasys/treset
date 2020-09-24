<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Helpers;

class VistaProveedor extends Model
{
    protected $table = 'VistaProveedores';
    protected $fillable = [
        'Numero',
        'Rfc',
        'Nombre',
        'CodigoPostal',
        'Email1',
        'Plazo',
        'Telefonos',
        'Status'
    ];
}
