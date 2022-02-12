<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ServiciosExport;
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
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Codigo .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Codigo .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Costo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('Venta', function($data){
                        return Helpers::convertirvalorcorrecto($data->Venta);
                    })
                    ->addColumn('Cantidad', function($data){
                        return Helpers::convertirvalorcorrecto($data->Cantidad);
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
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
    //obtener familias por numero
    public function servicios_obtener_familia_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existefamilia = Familia::where('Numero', $request->familia)->count();
        if($existefamilia > 0){
            $familia = Familia::where('Numero', $request->familia)->first();
            $numero = $familia->Numero;
            $nombre = $familia->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener claves productos
    public function servicios_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    }
    //obtener clave producto por clave
    public function servicios_obtener_clave_producto_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeclaveproducto = ClaveProdServ::where('Clave', $request->claveproducto)->count();
        if($existeclaveproducto > 0){
            $claveproducto = ClaveProdServ::where('Clave', $request->claveproducto)->first();
            $clave = $claveproducto->Clave;
            $nombre = $claveproducto->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
    }
    //obtener claves unidades
    public function servicios_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $data = ClaveUnidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    }   
    //obtener clave unidad por clave
    public function servicios_obtener_clave_unidad_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeclaveunidad = ClaveUnidad::where('Clave', $request->claveunidad)->count();
        if($existeclaveunidad > 0){
            $claveunidad = ClaveUnidad::where('Clave', $request->claveunidad)->first();
            $clave = $claveunidad->Clave;
            $nombre = $claveunidad->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data); 
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
        if($servicio == null){
            $servicio = Servicio::where('Codigo','LIKE', '%'.$request->codigoservicio.'%')->first();
        }
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
        Servicio::where('Codigo', $codigo)
                    ->update([
                        'Servicio' => $request->servicio,
                        'Unidad' => $request->unidad,
                        'Familia' => $request->familia,
                        'Costo' => $request->costo,
                        'Venta' => $request->venta,
                        'Cantidad' => $request->cantidad,
                        'ClaveProducto' => $request->claveproducto,
                        'ClaveUnidad' => $request->claveunidad
                    ]);
        /*
		$Servicio->Servicio=$request->servicio;
		$Servicio->Unidad=$request->unidad;
        $Servicio->Familia=$request->familia;
        $Servicio->Costo=$request->costo;
        $Servicio->Venta=$request->venta;
        $Servicio->Cantidad=$request->cantidad;
        $Servicio->ClaveProducto=$request->claveproducto;
        $Servicio->ClaveUnidad=$request->claveunidad;	  
        */
        Log::channel('servicio')->info('Se modifico el servicio: '.$Servicio.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		//$Servicio->save();
    	return response()->json($Servicio); 
    }    
    //exportar a excel
    public function servicios_exportar_excel(){
        return Excel::download(new ServiciosExport, 'servicios.xlsx');
    }
}
