<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Rel_Menu extends Model
{
    protected $table = 'user_rel_menus';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 
        'menu_id',
        'status',
    ];
}
