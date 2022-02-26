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
use App\Personal;
use DB;

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
            $data = Tecnico::query();
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
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos('.$data->Numero.')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar('.$data->Numero.')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
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
        //dar de alta en tabla personal
        DB::unprepared('SET IDENTITY_INSERT personal ON');
        $id = Helpers::ultimoidregistrotabla('App\Personal');
        $Personal = new Personal;
        $Personal->id=$id;
        $Personal->nombre = $request->nombre;
        $Personal->fecha_ingreso = Carbon::now()->toDateTimeString();
        $Personal->tipo_personal = "Técnico";
        $Personal->status = 'ALTA';
        $Personal->save();
        DB::unprepared('SET IDENTITY_INSERT personal OFF');
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
        Tecnico::where('Numero', $numerotecnico)
                    ->update([
                        'Nombre' => $request->nombre,
                        'Objetivo' => $request->objetivo,
                        'Planeacion' => $request->planeacion
                    ]);
        /*
        $Tecnico->Nombre=$request->nombre;   
        $Tecnico->Objetivo=$request->objetivo; 
        $Tecnico->Planeacion=$request->planeacion;
        */
        Log::channel('tecnico')->info('Se modifico el tecnico: '.$Tecnico.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		//$Tecnico->save();
    	return response()->json($Tecnico); 
    }
    //exportar a excel
    public function tecnicos_exportar_excel(){
        return Excel::download(new TecnicosExport, 'tecnicos.xlsx');
    }
}
