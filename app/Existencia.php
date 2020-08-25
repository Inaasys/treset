<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Existencia extends Model
{
    public $timestamps = false;
    protected $table = 'Existencias';
    protected $primaryKey = 'Codigo';
    protected $fillable = [
        'Codigo', 
        'Almacen',
        'Existencias'
    ];
}
