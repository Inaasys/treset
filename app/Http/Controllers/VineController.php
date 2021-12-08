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
use App\Exports\VinesExport;
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
            /*
            $data = DB::table('Vines AS v')
            ->leftJoin('Clientes AS c', 'c.Numero', '=', 'v.Cliente')
            ->select('c.Nombre AS Cliente', 'v.Economico', 'v.Vin', 'v.Placas', 'v.Marca', 'v.Modelo', 'v.Motor', 'v.Año', 'v.Color', 'v.Status')
            ->get();
            */
            $data = Vine::select('Cliente', 'Economico', 'Vin', 'Placas', 'Marca', 'Modelo', 'Motor', 'Año', 'Color', 'Status');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Vin .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Vin .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
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
    //obtener cliente por numero
    public function vines_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->cliente)->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->cliente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
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
        Vine::where('Vin', $numerovin)
                    ->update([
                        'Economico' => $request->economico,
                        'Cliente' => $request->cliente,
                        'Placas' => $request->placas,
                        'Motor' => $request->motor,
                        'Marca' => $request->marca,
                        'Modelo' => $request->modelo,
                        'Año' => $request->ano,
                        'Color' => $request->color
                    ]);
        /*
        $Vin->Economico=$request->economico;
        $Vin->Cliente=$request->cliente;
        $Vin->Placas=$request->placas;
        $Vin->Motor=$request->motor;
        $Vin->Marca=$request->marca;
        $Vin->Modelo=$request->modelo;
        $Vin->Año=$request->ano;
        $Vin->Color=$request->color;	
        */  
        Log::channel('vin')->info('Se modifico el vin: '.$Vin.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		//$Vin->save();
    	return response()->json($Vin); 
    }
    //exportar a excel
    public function vines_exportar_excel(){
        return Excel::download(new VinesExport, 'vines.xlsx');
    }  
}
