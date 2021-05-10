<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProveedoresExport;
use App\Proveedor;
use App\CodigoPostal;
use App\Configuracion_Tabla;
use App\VistaProveedor;

class ProveedorController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Proveedores')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function proveedores(){
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('proveedores_guardar_configuracion_tabla');
        return view('catalogos.proveedores.proveedores', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener todos los registros
    public function proveedores_obtener(Request $request){
        if($request->ajax()){
            $data = VistaProveedor::select($this->campos_consulta)->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            //$boton =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Numero.')">Altas</div>';
                        } 
                        return $boton;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }  
    //obtener ultimo numero de tabla
    public function proveedores_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Proveedor');
        return response()->json($id);
    } 
    //obtener codigos postales
    public function proveedores_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcodigopostal(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    } 
    //guardar en catalogo
    public function proveedores_guardar(Request $request){
	    $rfc=$request->rfc;
	    $ExisteProveedor = Proveedor::where('Rfc', $rfc )->first();
	    if($ExisteProveedor == true){
	        $Proveedor = 1;
	    }else{
            //obtener el ultimo id de la tabla
            $id = Helpers::ultimoidtabla('App\Proveedor');
		    $Proveedor = new Proveedor;
		    $Proveedor->Numero=$id;
		    $Proveedor->Nombre=$request->nombre;
		    $Proveedor->Rfc=$request->rfc;
            $Proveedor->CodigoPostal=$request->codigopostal;
            $Proveedor->Email1=$request->email1;
            $Proveedor->Plazo=$request->plazo;
            $Proveedor->Telefonos=$request->telefonos;
            $Proveedor->Status='ALTA';
            Log::channel('proveedor')->info('Se registro un nuevo proveedor: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Proveedor->save();
      	}
        return response()->json($Proveedor); 
    } 
    //dar de baja o alta en catalogo
    public function proveedores_alta_o_baja(Request $request){
        $numeroproveedor=$request->numeroproveedor;
	    $Proveedor = Proveedor::where('Numero', $numeroproveedor )->first();
	    if($Proveedor->Status == 'ALTA'){
           $Proveedor->Status = 'BAJA';
           Log::channel('proveedor')->info('El proveedor fue dado de baja: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
	       $Proveedor->Status = 'ALTA';
           Log::channel('proveedor')->info('El proveedor fue dado de alta: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Proveedor->save();
	    return response()->json($Proveedor);
    } 
    //obtener datos del catalogo
    public function proveedores_obtener_proveedor(Request $request){
        $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->first();
        $data = array(
            "proveedor" => $proveedor
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function proveedores_guardar_modificacion(Request $request){
        $rfc=$request->rfc;
        $numeroproveedor= $request->numero;
	    $ExisteProveedor = Proveedor::where('Numero','<>', $numeroproveedor)->where('Rfc', $rfc )->first();
	    if($ExisteProveedor == true){
            $Proveedor = 1;
	    }else{
            //modificar registro
            $Proveedor = Proveedor::where('Numero', $numeroproveedor )->first();
		    $Proveedor->Nombre=$request->nombre;
		    $Proveedor->Rfc=$request->rfc;
            $Proveedor->CodigoPostal=$request->codigopostal;
            $Proveedor->Email1=$request->email1;
            $Proveedor->Plazo=$request->plazo;
            $Proveedor->Telefonos=$request->telefonos;	  
            Log::channel('proveedor')->info('Se modifico el proveedor: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		    $Proveedor->save();
      	}
    	return response()->json($Proveedor); 
    } 
    //exportar a excel
    public function proveedores_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ProveedoresExport($this->campos_consulta), "proveedores.xlsx");  
    }  
    //guardar configuracion tabla
    public function proveedores_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'Proveedores')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('proveedores');
    }  
}
