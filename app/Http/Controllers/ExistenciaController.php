<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DB;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExistenciasExport;
use App\Producto;
use App\Tabla;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Almacen;
use App\Existencia;
use App\Configuracion_Tabla;
use App\VistaExistencia;
use PDF;

class ExistenciaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function existencias(){
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Existencias', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('existencias_guardar_configuracion_tabla');
        return view('catalogos.existencias.existencias', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }

    //obtener todos los registros
    public function existencias_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Existencias', Auth::user()->id);
            $data = VistaExistencia::select($configuraciones_tabla['campos_consulta'])->where('Almacen', 1);
            return DataTables::of($data)
                    ->order(function ($query) use($configuraciones_tabla) {
                        if($configuraciones_tabla['configuracion_tabla']->primerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->primerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formaprimerordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->segundoordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->segundoordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formasegundoordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->tercerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->tercerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formatercerordenamiento . '');
                        }
                    })
                    ->withQuery('sumacosto', function($data) {
                        return $data->sum('Costo');
                    })
                    ->withQuery('sumasubtotal', function($data) {
                        return $data->sum('SubTotal');
                    })
                    ->withQuery('sumaiva', function($data) {
                        return $data->sum('Iva');
                    })
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('Total');
                    })
                    ->withQuery('sumatotalcostoinventario', function($data) {
                        return $data->sum('totalCostoInventario');
                    })
                    ->withQuery('sumacostolista', function($data) {
                        return $data->sum('CostoDeLista');
                    })
                    ->withQuery('sumacostoventa', function($data) {
                        return $data->sum('CostoDeVenta');
                    })
                    ->withQuery('sumaexistencias', function($data) {
                        return $data->sum('Existencias');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '';
                        return $operaciones;
                    })
                    ->addColumn('Existencias', function($data){ return $data->Existencias; })
                    ->addColumn('Costo', function($data){ return $data->Costo; })
                    ->addColumn('totalCostoInventario', function($data){ return $data->totalCostoInventario; })
                    ->addColumn('CostoDeLista', function($data){ return $data->CostoDeLista; })
                    ->addColumn('CostoDeVenta', function($data){ return $data->CostoDeVenta; })
                    ->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Precio', function($data){ return $data->Precio; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 

    //exportar excel
    public function existencias_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Existencias', Auth::user()->id);
        return Excel::download(new ExistenciasExport($configuraciones_tabla['campos_consulta']), "existencias.xlsx");
    }
    
    //guardar configuracion tabla
    public function existencias_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Existencias', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Existencias')->where('IdUsuario', Auth::user()->id)
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
        }else{
            $Configuracion_Tabla=new Configuracion_Tabla;
            $Configuracion_Tabla->tabla='Existencias';
            $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
            $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
            $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
            $Configuracion_Tabla->ordenar = 0;
            $Configuracion_Tabla->usuario = Auth::user()->user;
            $Configuracion_Tabla->campos_busquedas = substr($selectmultiple, 1);
            $Configuracion_Tabla->primerordenamiento = $request->selectorderby1;
            $Configuracion_Tabla->formaprimerordenamiento = $request->deorderby1;
            $Configuracion_Tabla->segundoordenamiento =  $request->selectorderby2;
            $Configuracion_Tabla->formasegundoordenamiento =  $request->deorderby2;
            $Configuracion_Tabla->tercerordenamiento = $request->selectorderby3;
            $Configuracion_Tabla->formatercerordenamiento = $request->deorderby3;
            $Configuracion_Tabla->IdUsuario = Auth::user()->id;
            $Configuracion_Tabla->save();
        }
        return redirect()->route('existencias');
    }

    
}
