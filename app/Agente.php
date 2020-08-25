<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agente extends Model
{
    public $timestamps = false;
    protected $table = 'Agentes';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre', 
        'Direccion',
        'Colonia',
        'Ciudad',
        'Cp',
        'Rfc',
        'Contacto',
        'Telefonos',
        'Email',
        'Cuenta',
        'Anotaciones',
        'Comision',
        'Dias1',
        'Dias2',
        'Comision1',
        'Dias3',
        'Dias4',
        'Comision2',
        'Dias5',
        'Dias6',
        'Comision3',
        'Dias7',
        'Dias8',
        'Comision4',
        'Almacen',
        'Status'
    ];
}