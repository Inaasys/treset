<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Helpers;
use DataTables;

class SesionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function sesiones(Request $request){
        return view('sessions.sessions');
    }

    public function sesiones_obtener(Request $request){
        if($request->ajax()){
            $data = DB::table('sessions as s')
                        ->leftjoin('users as u', 'u.id', '=', 's.user_id')
                        ->select('u.id AS usuario', 's.user_name', 's.device', 's.browser', 's.platform', 's.ip_address AS ip_dispositivo', 's.last_activity AS ultima_conexion', 's.id as id_session', 's.status')
                        //->where('user_id', auth()->id())
                        ->orderBy('s.last_activity', 'DESC')
                        ->get();
            $sesion = \Session::getId();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data) use($sesion){
                    if($data->id_session == $sesion){
                        $operaciones = '<button type="button" name="button" class="btn btn-xs btn-block btn-info delete-session" onclick="desactivar(\''.$data->id_session .'\')">Sesion Actual</button>';
                    }else{
                        $operaciones = '<button type="button" name="button" class="btn btn-xs btn-block btn-danger delete-session" onclick="desactivar(\''.$data->id_session .'\')">Eliminar Sesion</button>';
                    }
                    return $operaciones;
                })
                ->addColumn('ultima_conexion', function($data){ return Carbon::createFromTimeStamp($data->ultima_conexion)->diffForhumans(); })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 

    }
    //elimiar sesion
    public function eliminar_session(Request $request){
        DB::table('sessions')
            ->where('id', $request->sesiondesactivar)
            //->where('user_id', auth()->id())
            ->delete();
    }
    //eliminar sesiones activas
    public function sesiones_eliminar_sesiones_activas(Request $request){
        DB::table('sessions')
            ->where('status', 'Sesion Iniciada')
            ->delete(); 
        $resultado = true;
        return response()->json($resultado);  
    }
    //eliminar sesiones sin loguear
    public function sesiones_eliminar_sesiones_sin_login(Request $request){
        DB::table('sessions')
            ->where('status', 'No Inicio Sesion')
            ->delete(); 
        $resultado = true;
        return response()->json($resultado);  
    }
}
