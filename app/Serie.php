<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    public $timestamps = false;
    protected $table = 'Series';
    protected $primaryKey = 'Item';
    protected $fillable = [
        'Item', 
        'Documento',
        'Serie',
        'Nombre',
        'Usuario'
    ];
}
