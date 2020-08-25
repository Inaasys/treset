<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Piensa extends Model
{
    public $timestamps = false;
    protected $table = 'Piensa';
    protected $primaryKey = 'Numero';
    protected $fillable = [
        'Numero', 
        'Mensaje'
    ];
}
