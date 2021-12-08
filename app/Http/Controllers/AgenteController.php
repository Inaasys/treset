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
            $data = Agente::query();
            return DataTables::of($data)
                    ->order(function ($query) {
                        $query->orderBy('Numero', 'DESC');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos('.$data->Numero.')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar('.$data->Numero.')">Bajas</a></li>'.
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
    //obtener almacen por numero
    public function agentes_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->almacen)->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->almacen)->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data); 
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
            Agente::where('Numero', $numeroagente)
            ->update([
                'Nombre'=> $request->nombre,
                'Direccion'=> $request->direccion,
                'Colonia'=> $request->colonia,
                'Ciudad'=> $request->ciudad,
                'Cp'=> $request->codigopostal,
                'Rfc'=> $request->rfc,
                'Contacto'=> $request->contacto,
                'Telefonos'=> $request->telefonos,
                'Email'=> $request->email,   
                'Cuenta'=> $request->cuenta,
                'Almacen'=> $request->almacen,
                'Comision'=> $request->comision,
                'Anotaciones'=> $request->anotacione,
            ]);
            /*
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
            */
            Log::channel('agente')->info('Se modifico el agente: '.$Agente.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	    
		    //$Agente->save();
      	}
    	return response()->json($Agente); 
    }  
    //exportar a excel
    public function agentes_exportar_excel(){
        return Excel::download(new AgentesExport, 'agentes.xlsx');
    }                 
}
