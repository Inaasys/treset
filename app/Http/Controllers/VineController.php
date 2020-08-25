<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Helpers;
use DataTables;
use App\Vine;
use App\Cliente;

class VineController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function vines(){
        return view('catalogos.vines.vines');
    }
    //obtener todos los registros
    public function vines_obtener(Request $request){
        if($request->ajax()){
            $data = DB::table('Vines AS v')
            ->leftJoin('Clientes AS c', 'c.Numero', '=', 'v.Cliente')
            ->select('c.Nombre AS Cliente', 'v.Economico AS Economico', 'v.Vin AS Vin', 'v.Placas AS Placas', 'v.Marca AS Marca', 'v.Modelo AS Modelo', 'v.Motor AS Motor', 'v.Año AS Año', 'v.Color AS Color', 'v.Status AS Status')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Vin.')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Vin.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            //$boton =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Vin.')">Altas</div>';
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
    //obtener codigos postales
    public function vines_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Saldo', function($data){ 
                        $saldo = Helpers::convertirvalorcorrecto($data->Saldo);
                        return $saldo; 
                    })
                    ->rawColumns(['operaciones', 'Saldo'])
                    ->make(true);
        }        
    } 
    //guardar en catalogo
    public function vines_guardar(Request $request){
	    $vin=$request->vin;
	    $ExisteVin = Vine::where('Vin', $vin )->first();
	    if($ExisteVin == true){
	        $Vin = 1;
	    }else{
		    $Vin = new Vine;
		    $Vin->Vin=$request->vin;
		    $Vin->Economico=$request->economico;
		    $Vin->Cliente=$request->cliente;
            $Vin->Placas=$request->placas;
            $Vin->Motor=$request->motor;
            $Vin->Marca=$request->marca;
            $Vin->Modelo=$request->modelo;
            $Vin->Año=$request->ano;
            $Vin->Color=$request->color;
            $Vin->Status='ALTA';
            Log::channel('vin')->info('Se registro un nuevo vin: '.$Vin.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Vin->save();
      	}
        return response()->json($Vin); 
    } 
    //dar de baja o alta en catalogo
    public function vines_alta_o_baja(Request $request){
        $numerovin=$request->numerovin;
	    $Vin = Vine::where('Vin', $numerovin )->first();
	    if($Vin->Status == 'ALTA'){
           $Vin->Status = 'BAJA';
           Log::channel('vin')->info('El vin fue dado de baja: '.$Vin.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
	       $Vin->Status = 'ALTA';
           Log::channel('vin')->info('El vin fue dado de alta: '.$Vin.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Vin->save();
	    return response()->json($Vin);
    } 
    //obtener datos del catalogo
    public function vines_obtener_vine(Request $request){
        $vin = Vine::where('Vin', $request->numerovin)->first();
        $cliente = Cliente::where('Numero', $vin->Cliente)->first();
        $data = array(
            "vin" => $vin,
            "cliente" => $cliente
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function vines_guardar_modificacion(Request $request){
        $numerovin= $request->vin;
        //modificar registro
        $Vin = Vine::where('Vin', $numerovin )->first();
        $Vin->Economico=$request->economico;
        $Vin->Cliente=$request->cliente;
        $Vin->Placas=$request->placas;
        $Vin->Motor=$request->motor;
        $Vin->Marca=$request->marca;
        $Vin->Modelo=$request->modelo;
        $Vin->Año=$request->ano;
        $Vin->Color=$request->color;	  
        Log::channel('vin')->info('Se modifico el vin: '.$Vin.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		$Vin->save();
    	return response()->json($Vin); 
    }  
}
