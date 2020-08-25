<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use App\Proveedor;
use App\CodigoPostal;

class ProveedorController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function proveedores(){
        return view('catalogos.proveedores.proveedores');
    }
    //obtener todos los registros
    public function proveedores_obtener(Request $request){
        if($request->ajax()){
            $data = Proveedor::orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            $boton =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Numero.')">Altas</div>';
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
}
