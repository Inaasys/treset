<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Helpers;
use DataTables;
use App\Servicio;
use App\Familia;
use App\ClaveProdServ;
use App\ClaveUnidad;

class ServicioController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function servicios(){
        return view('catalogos.servicios.servicios');
    }
    //obtener todos los registros
    public function servicios_obtener(Request $request){
        if($request->ajax()){
            $data = DB::table('Servicios as s')
            ->leftJoin('Familia as f', 'f.numero', '=', 's.familia')
            ->select('s.Codigo as Codigo', 's.Servicio as Servicio', 's.Unidad as Unidad', 'f.Numero as NumeroFamilia', 'f.nombre as Familia', 's.Costo as Costo', 's.Venta as Venta', 's.Cantidad as Cantidad', 's.ClaveProducto as ClaveProducto', 's.ClaveUnidad as ClaveUnidad', 's.Status as Status')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Codigo .'\')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Codigo .'\')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            //$boton =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar(\''.$data->Codigo .'\')">Altas</div>';
                        } 
                        return $boton;
                    })
                    ->addColumn('Costo', function($data){
                        $costo = Helpers::convertirvalorcorrecto($data->Costo);
                        return $costo;
                    })
                    ->addColumn('Venta', function($data){
                        $venta = Helpers::convertirvalorcorrecto($data->Venta);
                        return $venta;
                    })
                    ->addColumn('Cantidad', function($data){
                        $cantidad = Helpers::convertirvalorcorrecto($data->Cantidad);
                        return $cantidad;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones','Costo','Venta','Cantidad'])
                    ->make(true);
        } 
    }  
    //obtener codigos postales
    public function servicios_obtener_familias(Request $request){
        if($request->ajax()){
            $data = Familia::where('Status', 'ALTA')->orderBy('Numero', 'ASC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfamilia('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    } 
    //obtener claves productos
    public function servicios_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $data = ClaveProdServ::where('Usual', 'S')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    }
    //obtener claves unidades
    public function servicios_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $data = ClaveUnidad::where('Usual', 'S')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    }     
    //guardar en catalogo
    public function servicios_guardar(Request $request){
	    $codigo=$request->codigo;
	    $ExisteServicio = Servicio::where('Codigo', $codigo )->first();
	    if($ExisteServicio == true){
	        $Servicio = 1;
	    }else{
		    $Servicio = new Servicio;
		    $Servicio->Codigo=$codigo;
		    $Servicio->Servicio=$request->servicio;
		    $Servicio->Unidad=$request->unidad;
            $Servicio->Familia=$request->familia;
            $Servicio->Costo=$request->costo;
            $Servicio->Venta=$request->venta;
            $Servicio->Cantidad=$request->cantidad;
            $Servicio->ClaveProducto=$request->claveproducto;
            $Servicio->ClaveUnidad=$request->claveunidad;
            $Servicio->Status='ALTA';
            Log::channel('servicio')->info('Se registro un nuevo servicio: '.$Servicio.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Servicio->save();
      	}
        return response()->json($Servicio); 
    } 
    //dar de baja o alta en catalogo
    public function servicios_alta_o_baja(Request $request){
        $codigoservicio=$request->codigoservicio;
	    $Servicio = Servicio::where('Codigo', $codigoservicio )->first();
	    if($Servicio->Status == 'ALTA'){
           $Servicio->Status = 'BAJA';
           Log::channel('servicio')->info('El servicio fue dado de baja: '.$Servicio.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
	       $Servicio->Status = 'ALTA';
           Log::channel('servicio')->info('El servicio fue dado de alta: '.$Servicio.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Servicio->save();
	    return response()->json($Servicio);
    } 
    //obtener datos del catalogo
    public function servicios_obtener_servicio(Request $request){
        $servicio = Servicio::where('Codigo', $request->codigoservicio)->first();
        $familia = Familia::select('Numero', 'Nombre')->where('Numero', $servicio->Familia)->first();
        $claveproducto = ClaveProdServ::select('Clave', 'Nombre')->where('Clave', $servicio->ClaveProducto)->first();
        $claveunidad = ClaveUnidad::select('Clave', 'Nombre')->where('Clave', $servicio->ClaveUnidad)->first();
        $data = array(
            "servicio" => $servicio,
            "familia" => $familia,
            "claveproducto" => $claveproducto,
            "claveunidad" => $claveunidad,
            "cantidad" => Helpers::convertirvalorcorrecto($servicio->Cantidad),
            "costo" => Helpers::convertirvalorcorrecto($servicio->Costo),
            "venta" => Helpers::convertirvalorcorrecto($servicio->Venta)
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function servicios_guardar_modificacion(Request $request){
        $codigo=$request->codigo;
        //modificar registro
        $Servicio = Servicio::where('Codigo', $codigo )->first();
		$Servicio->Servicio=$request->servicio;
		$Servicio->Unidad=$request->unidad;
        $Servicio->Familia=$request->familia;
        $Servicio->Costo=$request->costo;
        $Servicio->Venta=$request->venta;
        $Servicio->Cantidad=$request->cantidad;
        $Servicio->ClaveProducto=$request->claveproducto;
        $Servicio->ClaveUnidad=$request->claveunidad;	  
        Log::channel('servicio')->info('Se modifico el servicio: '.$Servicio.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		$Servicio->save();
    	return response()->json($Servicio); 
    }    
}
