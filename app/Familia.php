<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    public $timestamps = false;
    protected $table = 'Familia';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Nombre',
        'Status'
    ];
}
