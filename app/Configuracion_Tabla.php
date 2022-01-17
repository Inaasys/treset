<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuracion_Tabla extends Model
{
    protected $table = 'configuracion_tablas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tabla', 
        'campos_activados',
        'campos_desactivados',
        'columnas_ordenadas',
        'ordenar',
        'usuario',
        'campos_busquedas',
        'primerordenamiento',
        'formaprimerordenamiento',
        'segundorordenamiento',
        'formasegundoordenamiento',
        'tercerordenamiento',
        'formatercerordenamiento',
        'IdUsuario'
    ];
}
