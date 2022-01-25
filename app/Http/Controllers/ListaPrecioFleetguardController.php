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
use App\ListaPrecioFleetguard;
use App\VistaListaPrecioFleetguard;
use App\Exports\PlantillasActualizarListaPreciosFleetguardExport;
use App\Exports\ListaPreciosFleetguardExport;
use App\Imports\ListaPreciosFleetguardImport;
use GuzzleHttp\Client;
use DNS1D;
use DNS2D;
use PDF;
use Mail;
use App;

class ListaPrecioFleetguardController  extends ConfiguracionSistemaController{

    public function __construct(){
        
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'ListaPreciosFleetguard')->first();
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

    public function lista_precios_fleetguard(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('lista_precios_fleetguard_guardar_configuracion_tabla');
        $urlgenerarplantilla = route('lista_precios_fleetguard_generar_plantilla');
        $urlgenerarformatoexcel = route('lista_precios_fleetguard_exportar_excel');
        return view('registros.listasprecios.fleetguard', compact('configuracion_tabla','rutaconfiguraciontabla','urlgenerarplantilla','urlgenerarformatoexcel'));
    }

    //obtener todos los registros
    public function lista_precios_fleetguard_obtener(Request $request){
        if($request->ajax()){
            $data = VistaListaPrecioFleetguard::select($this->campos_consulta);
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

    //generar plantilla 
    public function lista_precios_fleetguard_generar_plantilla(Request $request){
        return Excel::download(new PlantillasActualizarListaPreciosFleetguardExport(), "plantillaactualizarlistapreciosfleetguard.xlsx"); 
    }

    //actualizar costos
    public function lista_precios_fleetguard_actualizar_lista_precios_vs_excel(Request $request){
        ini_set('max_execution_time', 3600); // 60 minutos
        ini_set('memory_limit', '-1');
        $arrayexcel =  Excel::toArray(new ListaPreciosFleetguardImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $eliminarlistaprecios = ListaPrecioFleetguard::truncate();
        foreach($partidasexcel as $partida){
            if($rowexcel > 1){
                $buscarcodigoenlista = ListaPrecioFleetguard::where('NumeroParte', ''.$partida[0].'')->count();
                /*if($buscarcodigoenlista > 0){
                    ListaPrecioFleetguard::where('NumeroParte', ''.$partida[0].'')
                    ->update([
                        'PrecioPublico' => $partida[2],
                    ]);
                    $listaactualizada = ListaPrecioFleetguard::where('NumeroParte', ''.$partida[0].'')->first();
                }else{
                */
                    //insertar registro
                    if(Helpers::convertirvalorcorrecto($partida[2]) > 0){

                        $descuento = $partida[2] * 0.26;
                        $preciopublico = $partida[2] - $descuento;
                    }else{
                        $preciopublico = 0;
                    }
                    $ListaPrecioFleetguard = new ListaPrecioFleetguard;
                    $ListaPrecioFleetguard->NumeroParte = $partida[0];
                    $ListaPrecioFleetguard->Descripcion = $partida[1];
                    $ListaPrecioFleetguard->PrecioPublico = $preciopublico;
                    $ListaPrecioFleetguard->save();
                //}
            }
            $rowexcel++;
        }
        return response()->json($rowexcel); 
    }

    //exportar excel
    public function lista_precios_fleetguard_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('ListaPreciosFleetguard', Auth::user()->id);
        return Excel::download(new ListaPreciosFleetguardExport($configuraciones_tabla['campos_consulta']), "listapreciosfleetguard.xlsx");
    }

    //configurar tabalas
    public function lista_precios_fleetguard_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'ListaPreciosFleetguard')
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
        return redirect()->route('lista_precios_fleetguard');
    }
}
