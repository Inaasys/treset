<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoOrdenCompra extends Model
{
    public $timestamps = false;
    protected $table = 'TiposOrdenCompra';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];}
