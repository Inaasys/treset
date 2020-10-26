<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PersonalExport;
use App\Personal;
use App\Tecnico;
use App\User;
use DB;

class PersonalController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function personal(){
        $numeropersonal = Personal::orderBy('id', 'DESC')->count();
        return view('catalogos.personal.personal', compact('numeropersonal'));
    }
    //obtener todos los registros
    public function personal_obtener(Request $request){
        if($request->ajax()){
            $data = Personal::orderBy('id', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->status == 'ALTA'){
                            $boton = '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->id.')"><i class="material-icons">mode_edit</i></div> '. 
                            '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->id.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $boton = '';
                            //$boton = '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->id.')">Altas</div>';
                        } 
                        return $boton;
                    })
                    ->setRowClass(function ($data) {
                        return $data->status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }  
    //obtener todos los usuarios y tecnicos del sistema
    public function personal_obtener_usuarios_y_tecnicos(){
        $tecnicos = Tecnico::where('Status', '<>', 'BAJA')->orderBy('Numero', 'ASC')->get();
        $usuarios = User::where('status', '<>', 'BAJA')->orderBy('id', 'ASC')->get();
        $filaspersonal = '';
        $contadorfilas = 0;
        foreach($tecnicos as $t){
            $fechaingresotecnico = Carbon::now();
            $filaspersonal= $filaspersonal.
                '<tr class="filaspersonal" id="filapersonal'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapersonal('.$contadorfilas.')">X</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control nombrepersonal" name="nombrepersonal[]" value="'.$t->Nombre.'" readonly>'.$t->Nombre.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechaingresopersonal"  name="fechaingresopersonal[]" value="'.$fechaingresotecnico->toDateString().'">'.$fechaingresotecnico->toDateTimeString().'</td>'.
                    '<td class="tdmod">'.
                        '<select name="tipopersonal[]" class="form-control" style="width:100% !important;">'.
                            '<option value="Técnico" selected>Técnico</option>'.
                            '<option value="Administrativo">Administrativo</option>'.
                        '</select>'.
                    '</td>'.
                '</tr>';
                $contadorfilas++;
        }
        foreach($usuarios as $u){
            $filaspersonal= $filaspersonal.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapersonal('.$contadorfilas.')">X</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control nombrepersonal" name="nombrepersonal[]" value="'.$u->name.'" readonly>'.$u->name.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm fechaingresopersonal"  name="fechaingresopersonal[]" value="'.$u->created_at->toDateString().'">'.$u->created_at.'</td>'.
                    '<td class="tdmod">'.
                        '<select name="tipopersonal[]" class="form-control" style="width:100% !important;">'.
                            '<option value="Técnico">Técnico</option>'.
                            '<option value="Administrativo" selected>Administrativo</option>'.
                        '</select>'.
                    '</td>'.
                '</tr>';
                $contadorfilas++;        
        }
        return response()->json($filaspersonal);
    }
    //guardar usuarios y tecnicos
    public function personal_guardar_usuarios_y_tecnicos(Request $request){
        foreach ($request->nombrepersonal as $key => $nombre){ 
            $fechaingresopersonal = Carbon::now()->toDateTimeString();
            DB::unprepared('SET IDENTITY_INSERT personal ON');
            $id = Helpers::ultimoidregistrotabla('App\Personal');
            $Personal = new Personal;
            $Personal->id=$id;
            $Personal->nombre = $nombre;
            $Personal->fecha_ingreso = Carbon::now()->toDateTimeString();
            $Personal->tipo_personal = $request->tipopersonal [$key];
            $Personal->status = 'ALTA';
            $Personal->save();
            DB::unprepared('SET IDENTITY_INSERT personal OFF');
        }
        return redirect()->route('personal');
    }
    //alta o bajas de persona
    public function personal_alta_o_baja(Request $request){
        $numeropersonal=$request->numeropersonal;
	    $Personal = Personal::where('id', $numeropersonal )->first();
	    if($Personal->status == 'ALTA'){
	       $Personal->status = 'BAJA';
        }else{
	       $Personal->Status = 'ALTA';
        }
        $Personal->save();
	    return response()->json($Personal);
    }
    //obtener personal a modificar
    public function personal_obtener_personal(Request $request){
        $personal = Personal::where('id', $request->numeropersonal)->first();
        if($personal->tipo_personal == 'Administrativo' ){
            $tipopersonal = '<option value="Técnico">Técnico</option>'.
            '<option value="Administrativo" selected>Administrativo</option>';
        }else{
            $tipopersonal = '<option value="Técnico" selected>Técnico</option>'.
            '<option value="Administrativo" >Administrativo</option>';
        }
        $data = array(
            "personal" => $personal,
            "tipopersonal" => $tipopersonal
        );
        return response()->json($data);
    }
    //modificar el personal
    public function personal_guardar_modificacion(Request $request){
        $idpersonal= $request->id;
        //modificar registro
        $Personal = Personal::where('id', $idpersonal )->first();
        $Personal->nombre=$request->nombre;   
        $Personal->tipo_personal=$request->tipopersonal; 
		$Personal->save();
    	return response()->json($Personal); 
    }
    //exportar en excel el personal
    public function personal_exportar_excel(){
        return Excel::download(new PersonalExport, 'personal.xlsx');
    }
}
