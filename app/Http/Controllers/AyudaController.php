<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AyudaController extends ConfiguracionSistemaController
{
    
    //ruta ayuda sistema
    public function ayuda(){
        return view('ayuda.ayudavideos');
    }
}
