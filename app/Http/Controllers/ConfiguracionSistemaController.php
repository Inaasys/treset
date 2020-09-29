<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View; 
use Helpers;
use Carbon\Carbon;
use App\Empresa;
use App\TipoDeCambio;

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
        $this->meshoy = Carbon::now()->format('m'); //obtiene el mes actual ejemplo 09
        // datos empresa
        $this->empresa = Empresa::where('Numero', 1)->first();
        //obtener o actualizar el valor del dolar segun sea el caso
        $fechahoy = Carbon::now()->toDateString();
        $dia=date("w", strtotime($fechahoy));
        switch ($dia) {
            case "6":
                $fechaviernes = new Carbon('last friday');   
                $fecha = Carbon::parse($fechaviernes)->toDateString();
                break;
            case "0":
                $fechaviernes = new Carbon('last friday');   
                $fecha = Carbon::parse($fechaviernes)->toDateString();
                break;
            default:
                $fecha = $fechahoy;
        }
        $tipodecambio = TipoDeCambio::whereDate('Fecha', $fecha)->first();
        if($tipodecambio == null){
            $valor_dolar_dof = Helpers::obtener_valor_dolar_por_fecha_diario_oficial_federacion($fecha);
            $id = Helpers::ultimoidtabla('App\TipoDeCambio');
            $TipoDeCambio = new TipoDeCambio;
            $TipoDeCambio->Numero = $id;
            $TipoDeCambio->Moneda = 'USD';
            $TipoDeCambio->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $TipoDeCambio->TipoCambioDOF = $valor_dolar_dof;
            $TipoDeCambio->save();
        }else{
            $valor_dolar_dof = $tipodecambio->TipoCambioDOF;
        }
        $this->valor_dolar_hoy = $valor_dolar_dof;
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
        View::share ( 'valor_dolar_hoy', $this->valor_dolar_hoy);
        //View::share ( 'variable4', ['name'=>'Franky','address'=>'Mars'] );
    } 
}
