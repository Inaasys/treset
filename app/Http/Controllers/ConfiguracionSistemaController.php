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
        $this->numerodecimalesendocumentos = config('app.numerodecimalesendocumentos');//obtiene el numero de decimales configurados para los documentos pdf del sistema
        $this->numerocerosconfigurados = Helpers::numerocerosconfiguracion(); //obtienes los ceros que se deben colocar con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 000
        $this->numerocerosconfiguradosinputnumberstep = Helpers::numerocerosconfiguracioninputnumberstep(); //obtienes los ceros que se deben colocar en los input type number con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 001
        $this->periodoinicial = config('app.periodoincialmodulos');//obtiene el año incial en las vistas principales de los modulo ejemplo 2014
        $this->periodohoy = Carbon::now()->format('Y'); //obtiene el año actual ejemplo 2020
        $this->meshoy = Carbon::now()->format('m'); //obtiene el mes actual ejemplo 09
        // datos empresa
        $this->empresa = Empresa::where('Numero', 1)->first();
        //datos empresa para pdfs
        $this->calleempresa = config('app.calleempresa');// obtiene la calle de la empresa
        $this->noexteriorempresa = config('app.noexteriorempresa');//obtiene el numero exterior de la empresa
        $this->coloniaempresa = config('app.coloniaempresa');//obtiene la colonia de la empresa
        $this->cpempresa = config('app.cpempresa');//obtiene el cp de la empresa
        $this->municipioempresa = config('app.municipioempresa');//obtiene el municipio de la empresa
        $this->estadoempresa = config('app.estadoempresa');//obtiene el estado de la empresa
        $this->telefonosempresa = config('app.telefonosempresa');//obtiene el telefono de la empresa
        //Para Emisor Documentos
        $this->lugarexpedicion = config('app.lugarexpedicion');//obtiene el lugar expedicion
        $this->regimenfiscal = config('app.regimenfiscal');//obtiene el regimen fiscal
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
            $estado_conexion_internet = Helpers::comprobar_conexion_internet();
            if($estado_conexion_internet == true){
                $valor_dolar_dof = Helpers::obtener_valor_dolar_por_fecha_diario_oficial_federacion($fecha);
                if($valor_dolar_dof == "sinactualizacion"){
                    $tipodecambio = TipoDeCambio::orderBy('Numero', 'DESC')->first();
                    $valor_dolar_dof = $tipodecambio->TipoCambioDOF;
                }else{
                    $id = Helpers::ultimoidtabla('App\TipoDeCambio');
                    $TipoDeCambio = new TipoDeCambio;
                    $TipoDeCambio->Numero = $id;
                    $TipoDeCambio->Moneda = 'USD';
                    $TipoDeCambio->Fecha = Helpers::fecha_exacta_accion_datetimestring();
                    $TipoDeCambio->TipoCambioDOF = $valor_dolar_dof;
                    $TipoDeCambio->save();
                }
            }else{
                $tipodecambio = TipoDeCambio::orderBy('Numero', 'DESC')->first();
                $valor_dolar_dof = $tipodecambio->TipoCambioDOF;
            }
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
        //para pdfs
        View::share ( 'calleempresa', $this->calleempresa);
        View::share ( 'noexteriorempresa', $this->noexteriorempresa);
        View::share ( 'coloniaempresa', $this->coloniaempresa);
        View::share ( 'cpempresa', $this->cpempresa);
        View::share ( 'municipioempresa', $this->municipioempresa);
        View::share ( 'estadoempresa', $this->estadoempresa);
        View::share ( 'telefonosempresa', $this->telefonosempresa);
        //View::share ( 'variable4', ['name'=>'Franky','address'=>'Mars'] );
    } 
}
