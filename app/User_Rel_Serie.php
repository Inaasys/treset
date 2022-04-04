<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Rel_Serie extends Model
{
    protected $table = 'user_rel_series';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 
        'serie_id',
        'documento_serie'
    ];
}
