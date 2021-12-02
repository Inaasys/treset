<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View; 
use Helpers;
use Carbon\Carbon;
use App\Empresa;
use App\TipoDeCambio;
use DB;

class ConfiguracionSistemaController extends Controller
{
    public function __construct() {
        //DATOS EMPRESA
        $this->empresa = Empresa::where('Numero', 1)->first();
        //actualizar datos de configuracion global
        config(['app.periodoincialmodulos' => $this->empresa->Periodo_Inicial_Modulos]);
        config(['app.numerodedecimales' => $this->empresa->Numero_Decimales]);
        config(['app.numerodecimalesendocumentos' => $this->empresa->Numero_Decimales_En_Documentos]);
        config(['app.mayusculas_sistema' => $this->empresa->Mayusculas_Sistema]);
        config(['app.calleempresa' => $this->empresa->Calle]);
        config(['app.noexteriorempresa' => $this->empresa->NoExterior]);
        config(['app.nointeriorempresa' => $this->empresa->NoInterior]);
        config(['app.coloniaempresa' => $this->empresa->Colonia]);
        config(['app.localidadempresa' => $this->empresa->Localidad]);
        config(['app.referenciaempresa' => $this->empresa->Referencia]);
        config(['app.cpempresa' => $this->empresa->LugarExpedicion]);
        config(['app.municipioempresa' => $this->empresa->Municipio]);
        config(['app.estadoempresa' => $this->empresa->Estado]);
        config(['app.telefonosempresa' => $this->empresa->Telefonos]);
        config(['app.paisempresa' => $this->empresa->Pais]);
        config(['app.emailempresa' => $this->empresa->Email]);
        config(['app.lugarexpedicion' => $this->empresa->LugarExpedicion]);
        config(['app.regimenfiscal' => $this->empresa->RegimenFiscal]);
        config(['app.tipodeutilidad' => $this->empresa->Tipo_De_Utilidad]);
        config(['app.correodefault1enviodocumentos' => $this->empresa->CorreoDefault1EnvioDocumentos]);
        config(['app.correodefault2enviodocumentos' => $this->empresa->CorreoDefault2EnvioDocumentos]);
        config(['app.usuariosamodificarinsumos' => $this->empresa->UsuariosModificarInsumo]);
        ////////////OBTENER CONFIGURACIONES DEL SISTEMA////////////////
        $this->numerocerosconfigurados = Helpers::numerocerosconfiguracion(); //obtienes los ceros que se deben colocar con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 000
        $this->numerocerosconfiguradosinputnumberstep = Helpers::numerocerosconfiguracioninputnumberstep(); //obtienes los ceros que se deben colocar en los input type number con base a los decimales configurados en el sistemas ejemplo decimales para el sistema = 3 numero de ceros = 001
        $this->periodohoy = Carbon::now()->format('Y'); //obtiene el año actual ejemplo 2020
        $this->meshoy = Carbon::now()->format('m'); //obtiene el mes actual ejemplo 09
        $this->periodoinicial = config('app.periodoincialmodulos');//obtiene el año incial en las vistas principales de los modulo ejemplo 2014
        $this->numerodecimales = config('app.numerodedecimales');// obtiene el numero de decimales condigurados para el sistema
        $this->numerodecimalesendocumentos = config('app.numerodecimalesendocumentos');//obtiene el numero de decimales configurados para los documentos pdf del sistema
        $this->mayusculas_sistema = config('app.mayusculas_sistema');//obtiene si el sistema utilizara mayusculas o no
        //datos empresa para pdfs
        $this->calleempresa = config('app.calleempresa');// obtiene la calle de la empresa
        $this->noexteriorempresa = config('app.noexteriorempresa');//obtiene el numero exterior de la empresa
        $this->nointeriorempresa = config('app.nointeriorempresa');//obtiene el numero interior de la empresa
        $this->coloniaempresa = config('app.coloniaempresa');//obtiene la colonia de la empresa
        $this->localidadempresa = config('app.localidadempresa');//obtiene la localidad de la empresa
        $this->referenciaempresa = config('app.referenciaempresa');//obtiene la refenrecia de la empresa
        $this->cpempresa = config('app.cpempresa');//obtiene el cp de la empresa
        $this->municipioempresa = config('app.municipioempresa');//obtiene el municipio de la empresa
        $this->estadoempresa = config('app.estadoempresa');//obtiene el estado de la empresa
        $this->telefonosempresa = config('app.telefonosempresa');//obtiene el telefono de la empresa
        $this->paisempresa = config('app.paisempresa');//obtiene pais de la empresa
        $this->emailempresa = config('app.emailempresa');//obtiene pais de la empresa
        //Para Emisor Documentos
        $this->lugarexpedicion = config('app.lugarexpedicion');//obtiene el lugar expedicion
        $this->regimenfiscal = config('app.regimenfiscal');//obtiene el regimen fiscal
        //tipo utilidad
        $this->tipodeutilidad = config('app.tipodeutilidad');
        //correo por default en envios de documentos
        $this->correodefault1enviodocumentos = config('app.correodefault1enviodocumentos');
        $this->correodefault2enviodocumentos = config('app.correodefault2enviodocumentos');
        //usuarios ue puedes modificar isnumos 
        $this->usuariosamodificarinsumos = config('app.usuariosamodificarinsumos');
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
        //timbres ingreso - facturas totales activos utilizados
        $this->timbresingresofacturastotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Factura')
                        ->where('Tipo', 'I')
                        ->where('IdFacturapi', '<>', NULL)
                        ->count();
        //timbres ingreso - facturas totales canceladas utilizados
        $this->timbresingresofacturascanceladastotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Factura')
                        ->where('Tipo', 'I')
                        ->where('IdFacturapi', '<>', NULL)
                        ->where('FechaCancelacion', '<>', NULL)
                        ->count();
        //timbres egreso - notas totales activos utilizados
        $this->timbresegresonotastotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Nota')
                        ->where('Tipo', 'E')
                        ->where('IdFacturapi', '<>', NULL)
                        ->count();
        //timbres egreso - notas totales canceladas utilizados
        $this->timbresegresonotascanceladastotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Nota')
                        ->where('Tipo', 'E')
                        ->where('IdFacturapi', '<>', NULL)
                        ->where('FechaCancelacion', '<>', NULL)
                        ->count();
        //timbres pago - cxc totales activos utilizados
        $this->timbrespagocxctotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Pago')
                        ->where('Tipo', 'P')
                        ->where('IdFacturapi', '<>', NULL)
                        ->count();
        //timbres pago - cxc totales canceladas utilizados
        $this->timbrespagocxccanceladastotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('Comprobante', 'Pago')
                        ->where('Tipo', 'P')
                        ->where('IdFacturapi', '<>', NULL)
                        ->where('FechaCancelacion', '<>', NULL)
                        ->count();
        //timbres totales activos utilizados
        $this->timbrestotalesactivosfacturapi = DB::table('Comprobantes')
                        ->where('IdFacturapi', '<>', NULL)
                        ->count();
        //timbres totales cancelados
        $this->timbrestotalescanceladosfacturapi = DB::table('Comprobantes')
                        ->where('IdFacturapi', '<>', NULL)
                        ->where('FechaCancelacion', '<>', NULL)
                        ->count();
        ////////////FIN OBTENER CONFIGURACIONES DEL SISTEMA////////////////
        ///////////COMPARTIR CONFIGURACIONES EN TODAS LAS VISTAS ///////////
        View::share ( 'mayusculas_sistema', $this->mayusculas_sistema );
        View::share ( 'numerodecimales', $this->numerodecimales );
        View::share ( 'numerodecimalesendocumentos', $this->numerodecimalesendocumentos );
        View::share ( 'numerocerosconfigurados', $this->numerocerosconfigurados);
        View::share ( 'numerocerosconfiguradosinputnumberstep', $this->numerocerosconfiguradosinputnumberstep);
        View::share ( 'periodoinicial', $this->periodoinicial);
        View::share ( 'periodohoy', $this->periodohoy);
        View::share ( 'empresa', $this->empresa);
        View::share ( 'meshoy', $this->meshoy);
        View::share ( 'valor_dolar_hoy', $this->valor_dolar_hoy);
        View::share ( 'calleempresa', $this->calleempresa);
        View::share ( 'noexteriorempresa', $this->noexteriorempresa);
        View::share ( 'nointeriorempresa', $this->nointeriorempresa);
        View::share ( 'coloniaempresa', $this->coloniaempresa);
        View::share ( 'localidadempresa', $this->localidadempresa);
        View::share ( 'referenciaempresa', $this->referenciaempresa);
        View::share ( 'cpempresa', $this->cpempresa);
        View::share ( 'municipioempresa', $this->municipioempresa);
        View::share ( 'estadoempresa', $this->estadoempresa);
        View::share ( 'telefonosempresa', $this->telefonosempresa);
        View::share ( 'paisempresa', $this->paisempresa);
        View::share ( 'emailempresa', $this->emailempresa);
        View::share ( 'lugarexpedicion', $this->lugarexpedicion);
        View::share ( 'regimenfiscal', $this->regimenfiscal);
        View::share ( 'tipodeutilidad', $this->tipodeutilidad);
        View::share ( 'correodefault1enviodocumentos', $this->correodefault1enviodocumentos);
        View::share ( 'correodefault2enviodocumentos', $this->correodefault2enviodocumentos);
        View::share ( 'usuariosamodificarinsumos', $this->usuariosamodificarinsumos);

        View::share ( 'timbresingresofacturastotalesactivosfacturapi', $this->timbresingresofacturastotalesactivosfacturapi);
        View::share ( 'timbresingresofacturascanceladastotalesactivosfacturapi', $this->timbresingresofacturascanceladastotalesactivosfacturapi);
        View::share ( 'timbresegresonotastotalesactivosfacturapi', $this->timbresegresonotastotalesactivosfacturapi);
        View::share ( 'timbresegresonotascanceladastotalesactivosfacturapi', $this->timbresegresonotascanceladastotalesactivosfacturapi);
        View::share ( 'timbrespagocxctotalesactivosfacturapi', $this->timbrespagocxctotalesactivosfacturapi);
        View::share ( 'timbrespagocxccanceladastotalesactivosfacturapi', $this->timbrespagocxccanceladastotalesactivosfacturapi);
        View::share ( 'timbrestotalesactivosfacturapi', $this->timbrestotalesactivosfacturapi);
        View::share ( 'timbrestotalescanceladosfacturapi', $this->timbrestotalescanceladosfacturapi);


        //View::share ( 'array', ['name'=>'Franky','address'=>'Mars'] );
    } 
}
