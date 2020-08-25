<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vine extends Model
{
    public $timestamps = false;
    protected $table = 'Vines';
    protected $primaryKey = 'Vin';
    protected $fillable = [
        'Vin', 
        'Cliente', 
        'Economico',
        'Placas',
        'Motor',
        'Marca',
        'Modelo',
        'Año',
        'Color',
        'Status'
    ];
}
