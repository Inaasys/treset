<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class SesionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function sesiones(Request $request){
        $sessions = DB::table('sessions as s')
                        ->leftjoin('users as u', 'u.id', '=', 's.user_id')
                        ->select('u.user AS usuario', 's.user_agent AS dispositivo', 's.ip_address AS ip_dispositivo', 's.last_activity AS ultima_conexion', 's.id as id_session')
                        //->where('user_id', auth()->id())
                        ->orderBy('s.last_activity', 'DESC')
                        ->get();
        return view('sessions.sessions', ['sessions' => $sessions]);
    }

    public function eliminar_session(Request $request){
        DB::table('sessions')
            ->where('id', $request->id)
            //->where('user_id', auth()->id())
            ->delete();

    }
}
