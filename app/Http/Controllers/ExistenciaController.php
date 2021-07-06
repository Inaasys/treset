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
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Existencias')->first();
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

    public function existencias(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('existencias_guardar_configuracion_tabla');
        return view('catalogos.existencias.existencias', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }

    //obtener todos los registros
    public function existencias_obtener(Request $request){
        if($request->ajax()){
            //$data = VistaExistencia::select($this->campos_consulta)->where('Almacen', 1)->get();
            $data = VistaExistencia::select($this->campos_consulta)->where('Almacen', 1);
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
        return Excel::download(new ExistenciasExport($this->campos_consulta), "existencias.xlsx");
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
        Configuracion_Tabla::where('tabla', 'Existencias')
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
        return redirect()->route('existencias');
    }
    
}
