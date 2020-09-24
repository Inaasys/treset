<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgentesExport;
use App\Agente;
use App\Almacen;


class AgenteController extends ConfiguracionSistemaController{
    
    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function agentes(){
        return view('catalogos.agentes.agentes');
    }
    //obtener todos los registros
    public function agentes_obtener(Request $request){
        if($request->ajax()){
            $data = Agente::orderBy("Numero", "DESC")->get();
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
    public function agentes_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Agente');
        return response()->json($id);
    } 
    //obtener almacenes
    public function agentes_obtener_almacenes(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 
    //guardar en catalogo
    public function agentes_guardar(Request $request){
	    $rfc=$request->rfc;
	    $ExisteAgente = Agente::where('Rfc', $rfc )->first();
	    if($ExisteAgente == true){
	        $Agente = 1;
	    }else{
            //obtener el ultimo id de la tabla
            $id = Helpers::ultimoidtabla('App\Agente');
		    $Agente = new Agente;
		    $Agente->Numero=$id;
		    $Agente->Nombre=$request->nombre;
		    $Agente->Direccion=$request->direccion;
		    $Agente->Colonia=$request->colonia;
            $Agente->Ciudad=$request->ciudad;
		    $Agente->Cp=$request->codigopostal;
		    $Agente->Rfc=$request->rfc;
		    $Agente->Contacto=$request->contacto;
		    $Agente->Telefonos=$request->telefonos;
            $Agente->Email=$request->email;   
            $Agente->Cuenta=$request->cuenta;
            $Agente->Almacen=$request->almacen;
            $Agente->Comision=$request->comision;
            $Agente->Anotaciones=$request->anotaciones;
            $Agente->Status='ALTA';
            Log::channel('agente')->info('Se registro un nuevo agente: '.$Agente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Agente->save();
      	}
        return response()->json($Agente); 
    } 
    //dar de baja o alta en catalogo
    public function agentes_alta_o_baja(Request $request){
        $numeroagente=$request->numeroagente;
	    $Agente = Agente::where('Numero', $numeroagente )->first();
	    if($Agente->Status == 'ALTA'){
           $Agente->Status = 'BAJA';
           Log::channel('agente')->info('El agente fue dado de baja: '.$Agente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
           $Agente->Status = 'ALTA';
           Log::channel('agente')->info('El agente fue dado de alta: '.$Agente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }
	    $Agente->save();
	    return response()->json($Agente);
    } 
    //obtener datos del catalogo
    public function agentes_obtener_agente(Request $request){
        $agente = Agente::where('Numero', $request->numeroagente)->first();
        $numeroalmacen = $agente->Almacen;
        $almacen = Almacen::select('Numero', 'Nombre')->where('Numero', $numeroalmacen)->first();
        $comision = Helpers::convertirvalorcorrecto($agente->Comision);
        $data = array(
            "agente" => $agente,
            "numeroalmacen" => $numeroalmacen,
            "almacen" => $almacen,
            "comision" => $comision
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function agentes_guardar_modificacion(Request $request){
        $rfc=$request->rfc;
        $numeroagente = $request->numero;
	    $ExisteAgente = Agente::where('Numero','<>', $numeroagente)->where('Rfc', $rfc )->first();
	    if($ExisteAgente == true){
            $Agente = 1;
	    }else{
            //modificar registro
            $Agente = Agente::where('Numero', $numeroagente )->first();
		    $Agente->Nombre=$request->nombre;
		    $Agente->Direccion=$request->direccion;
		    $Agente->Colonia=$request->colonia;
            $Agente->Ciudad=$request->ciudad;
		    $Agente->Cp=$request->codigopostal;
		    $Agente->Rfc=$request->rfc;
		    $Agente->Contacto=$request->contacto;
		    $Agente->Telefonos=$request->telefonos;
            $Agente->Email=$request->email;   
            $Agente->Cuenta=$request->cuenta;
            $Agente->Almacen=$request->almacen;
            $Agente->Comision=$request->comision;
            $Agente->Anotaciones=$request->anotaciones;	
            Log::channel('agente')->info('Se modifico el agente: '.$Agente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	    
		    $Agente->save();
      	}
    	return response()->json($Agente); 
    }  
    //exportar a excel
    public function agentes_exportar_excel(){
        return Excel::download(new AgentesExport, 'agentes.xlsx');
    }                 
}
