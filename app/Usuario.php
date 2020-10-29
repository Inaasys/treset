<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    public $timestamps = false;
    protected $table = 'Usuarios';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'Usuario', 
    ];
}
