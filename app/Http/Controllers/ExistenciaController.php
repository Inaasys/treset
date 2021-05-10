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
            $data = VistaExistencia::select($this->campos_consulta)->get();
            return DataTables::of($data)
                    ->addColumn('Existencias', function($data){ return $data->Existencias; })
                    ->addColumn('Costo', function($data){ return $data->Costo; })
                    ->addColumn('CostoDeLista', function($data){ return $data->CostoDeLista; })
                    ->addColumn('CostoDeVenta', function($data){ return $data->CostoDeVenta; })
                    ->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('FechaUltimaCompra', function($data){ return $data->FechaUltimaCompra; })
                    ->addColumn('FechaUltimaVenta', function($data){ return $data->FechaUltimaVenta; })
                    ->addColumn('Precio', function($data){ return $data->Precio; })
                    ->rawColumns(['Existencias'])
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
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'Existencias')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('existencias');
    }
    
}
