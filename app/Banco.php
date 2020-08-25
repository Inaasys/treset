<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    public $timestamps = false;
    protected $table = 'Bancos';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Cuenta',
        'Status',
        'CuentaBancaria'
    ];
}
