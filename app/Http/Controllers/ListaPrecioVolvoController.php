<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use SoapClient;
use Helpers;
use DB;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use App\Producto;
use App\Tabla;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Marca;
use App\Linea;
use App\Moneda;
use App\Cliente;
use App\ProductoPrecio;
use App\ProductoConsumo;
use App\Almacen;
use App\Existencia;
use App\Configuracion_Tabla;
use App\VistaProducto;
use App\ContraReciboDetalle;
use App\TipoOrdenCompra;
use App\TipoCambioVolvo;
use App\TipoDeCambio;
use App\ListaPrecioVolvo;
use App\VistaListaPrecioVolvo;
use GuzzleHttp\Client;
use DNS1D;
use DNS2D;
use PDF;
use Mail;
use App;

class ListaPrecioVolvoController extends ConfiguracionSistemaController{

    public function __construct(){
        
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'ListaPreciosVolvo')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //campos vista
        $this->camposvista = [];
        foreach (explode(",", $this->configuracion_tabla->campos_activados) as $campo){
            array_push($this->camposvista, $campo);
        }
        foreach (explode(",", $this->configuracion_tabla->campos_desactivados) as $campo){
            array_push($this->camposvista, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function lista_precios_volvo(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('lista_precios_volvo_guardar_configuracion_tabla');
        $rutacrearpdfcodigosdebarrascatalogo = route('productos_generar_codigos_barras_catalogo');
        $rutacrearpdfcodigosdebarrasarray = route('productos_generar_codigos_barras_array');
        return view('registros.listasprecios.volvo', compact('configuracion_tabla','rutaconfiguraciontabla','rutacrearpdfcodigosdebarrascatalogo','rutacrearpdfcodigosdebarrasarray'));
    }


    //obtener todos los registros
    public function lista_precios_volvo_obtener(Request $request){
        if($request->ajax()){
            $data = VistaListaPrecioVolvo::select($this->campos_consulta);
            return DataTables::of($data)
                    ->order(function ($query) {
                        if($this->configuracion_tabla->primerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->primerordenamiento, '' . $this->configuracion_tabla->formaprimerordenamiento . '');
                        }
                        if($this->configuracion_tabla->segundoordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->segundoordenamiento, '' . $this->configuracion_tabla->formasegundoordenamiento . '');
                        }
                        if($this->configuracion_tabla->tercerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->tercerordenamiento, '' . $this->configuracion_tabla->formatercerordenamiento . '');
                        }
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Numero .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 



    //obtenr valor dolar ho de DOF
    public function lista_precios_volvo_obtener_valor_dolar_hoy_dof(){
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
        $valor_dolar_dof = Helpers::obtener_valor_dolar_por_fecha_diario_oficial_federacion($fecha);
        return response()->json($valor_dolar_dof);
    }

    //actualizar tipo cambio
    public function lista_precios_volvo_guardar_valor_tipo_cambio(Request $request){
        //insertar registro tipo cambios volvo
        if (App::environment('local') || App::environment('production')) {
            DB::unprepared('SET IDENTITY_INSERT tipo_cambio_volvo ON');
        }
        $id = Helpers::ultimoidtabla('App\TipoCambioVolvo');
        $TipoCambioVolvo = new TipoCambioVolvo;
        $TipoCambioVolvo->Numero = $id;
        $TipoCambioVolvo->Moneda = 'USD';
        $TipoCambioVolvo->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $TipoCambioVolvo->Valor = $request->valortipocambio;
        $TipoCambioVolvo->save();
        if (App::environment('local') || App::environment('production')) {
            DB::unprepared('SET IDENTITY_INSERT tipo_cambio_volvo OFF');
        }
        //insertar registro tabla general tipo cambios
        $id = Helpers::ultimoidtabla('App\TipoDeCambio');
        $TipoDeCambio = new TipoDeCambio;
        $TipoDeCambio->Numero = $id;
        $TipoDeCambio->Moneda = 'USD';
        $TipoDeCambio->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $TipoDeCambio->TipoCambioDOF = $request->valortipocambio;
        $TipoDeCambio->save();  
    }

    //configurar tabalas
    public function lista_precios_volvo_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'ListaPreciosVolvo')
        ->update([
            'campos_activados' => $request->string_datos_tabla_true,
            'campos_desactivados' => $string_datos_tabla_false,
            'columnas_ordenadas' => $request->string_datos_ordenamiento_columnas,
            'usuario' => Auth::user()->user,
            'primerordenamiento' => $request->selectorderby1,
            'formaprimerordenamiento' => $request->deorderby1,
            'segundoordenamiento' => $request->selectorderby2,
            'formasegundoordenamiento' => $request->deorderby2,
            'tercerordenamiento' => $request->selectorderby3,
            'formatercerordenamiento' => $request->deorderby3,
            'campos_busquedas' => substr($selectmultiple, 1),
        ]);
        return redirect()->route('lista_precios_volvo');
    }
}
