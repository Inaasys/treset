<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TecnicosExport;
use App\Tecnico;

class TecnicoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function tecnicos(){
        return view('catalogos.tecnicos.tecnicos');
    }
    //obtener todos los registros
    public function tecnicos_obtener(Request $request){
        if($request->ajax()){
            $data = Tecnico::orderBy('Numero', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status == 'ALTA'){
                            $boton = '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '. 
                            '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            //$boton = '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Numero.')">Altas</div>';
                        } 
                        return $boton;
                    })
                    ->addColumn('Objetivo', function($data){ 
                        $objetivo = Helpers::convertirvalorcorrecto($data->Objetivo);
                        return $objetivo;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones','Objetivo'])
                    ->make(true);
        } 
    }  
    //obtener ultimo numero de tabla
    public function tecnicos_obtener_ultimo_numero(){
        $data = Helpers::ultimoidtabla('App\Tecnico');
        return response()->json($data);
    } 
    //guardar en catalogo
    public function tecnicos_guardar(Request $request){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        //obtener el ultimo id de la tabla
        $id = Helpers::ultimoidtabla('App\Tecnico');
		$Tecnico = new Tecnico;
		$Tecnico->Numero=$id;
        $Tecnico->Nombre=$request->nombre;
        $Tecnico->Objetivo=$request->objetivo;
        $Tecnico->Planeacion=$request->planeacion;
        $Tecnico->Status='ALTA';
        Log::channel('tecnico')->info('Se registro un nuevo tecnico: '.$Tecnico.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Tecnico->save();
        return response()->json($Tecnico); 
    } 
    //dar de baja o alta en catalogo
    public function tecnicos_alta_o_baja(Request $request){
        $numerotecnico=$request->numerotecnico;
	    $Tecnico = Tecnico::where('Numero', $numerotecnico )->first();
	    if($Tecnico->Status == 'ALTA'){
	       $Tecnico->Status = 'BAJA';
           Log::channel('tecnico')->info('El tecnico fue dado de baja: '.$Tecnico.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Tecnico->Status = 'ALTA';
           Log::channel('tecnico')->info('El tecnico fue dado de alta: '.$Tecnico.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Tecnico->save();
	    return response()->json($Tecnico);
    } 
    //obtener datos del catalogo
    public function tecnicos_obtener_tecnico(Request $request){
        $tecnico = Tecnico::where('Numero', $request->numerotecnico)->first();
        $objetivo = Helpers::convertirvalorcorrecto($tecnico->Objetivo);
        $data = array(
            "tecnico" => $tecnico,
            "objetivo" => $objetivo
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function tecnicos_guardar_modificacion(Request $request){
        $numerotecnico= $request->numero;
        //modificar registro
        $Tecnico = Tecnico::where('Numero', $numerotecnico )->first();
        $Tecnico->Nombre=$request->nombre;   
        $Tecnico->Objetivo=$request->objetivo; 
        $Tecnico->Planeacion=$request->planeacion;
        Log::channel('tecnico')->info('Se modifico el tecnico: '.$Tecnico.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		$Tecnico->save();
    	return response()->json($Tecnico); 
    }
    //exportar a excel
    public function tecnicos_exportar_excel(){
        return Excel::download(new TecnicosExport, 'tecnicos.xlsx');
    }
}
