<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View; 
use Helpers;
use Carbon\Carbon;
use App\Empresa;

class ConfiguracionSistemaController extends Controller
{
    public function __construct() {

        ////////////OBTENER CONFIGURACIONES DEL SISTEMA////////////////
        $this->mayusculas_sistema = config('app.mayusculas_sistema');//obtiene si el sistema utilizara mayusculas o no
        $this->numerodecimales = config('app.numerodedecimales');// obtiene el numero de decimales condigurados para el sistema
        $this->numerocerosconfigurados = Helpers::numerocerosconfiguracion(); //obtienes los ceros que se deben colocar con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 000
        $this->numerocerosconfiguradosinputnumberstep = Helpers::numerocerosconfiguracioninputnumberstep(); //obtienes los ceros que se deben colocar en los input type number con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 001
        $this->periodoinicial = config('app.periodoincialmodulos');//obtiene el año incial en las vistas principales de los modulo ejemplo 2014
        $this->periodohoy = Carbon::now()->format('Y'); //obtiene el año actual ejemplo 2020
        $this->meshoy = Carbon::now()->format('m');
        // datos empresa
        $this->empresa = Empresa::where('Numero', 1)->first();
        ////////////FIN OBTENER CONFIGURACIONES DEL SISTEMA////////////////
        ///////////COMPARTIR CONFIGURACIONES EN TODAS LAS VISTAS ///////////
        View::share ( 'mayusculas_sistema', $this->mayusculas_sistema );
        View::share ( 'numerodecimales', $this->numerodecimales );
        View::share ( 'numerocerosconfigurados', $this->numerocerosconfigurados);
        View::share ( 'numerocerosconfiguradosinputnumberstep', $this->numerocerosconfiguradosinputnumberstep);
        View::share ( 'periodoinicial', $this->periodoinicial);
        View::share ( 'periodohoy', $this->periodohoy);
        View::share ( 'empresa', $this->empresa);
        View::share ( 'meshoy', $this->meshoy);
        //View::share ( 'variable4', ['name'=>'Franky','address'=>'Mars'] );
    } 
}
