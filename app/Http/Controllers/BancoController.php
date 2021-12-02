<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BancosExport;
use App\Banco;

class BancoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function bancos(){
        return view('catalogos.bancos.bancos');
    }
    //obtener todos los registros
    public function bancos_obtener(Request $request){
        if($request->ajax()){
            $data = Banco::query();
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
    public function bancos_obtener_ultimo_numero(){
        $data = Helpers::ultimoidycuentacontablebanco('App\Banco');
        return response()->json($data);
    } 
    //guardar en catalogo
    public function bancos_guardar(Request $request){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        //obtener el ultimo id de la tabla
        $id = Helpers::ultimoidtabla('App\Banco');
		$Banco = new Banco;
		$Banco->Numero=$id;
        $Banco->Nombre=$request->nombre;
        $Banco->Cuenta=$request->cuenta;
        $Banco->Status='ALTA';
        Log::channel('banco')->info('Se registro un nuevo banco: '.$Banco.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Banco->save();
        return response()->json($Banco); 
    } 
    //dar de baja o alta en catalogo
    public function bancos_alta_o_baja(Request $request){
        $numerobanco=$request->numerobanco;
	    $Banco = Banco::where('Numero', $numerobanco )->first();
	    if($Banco->Status == 'ALTA'){
	       $Banco->Status = 'BAJA';
           Log::channel('banco')->info('El banco fue dado de baja: '.$Banco.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Banco->Status = 'ALTA';
           Log::channel('banco')->info('El banco fue dado de alta: '.$Banco.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Banco->save();
	    return response()->json($Banco);
    } 
    //obtener datos del catalogo
    public function bancos_obtener_banco(Request $request){
        $banco = Banco::where('Numero', $request->numerobanco)->first();
        $data = array(
            "banco" => $banco
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function bancos_guardar_modificacion(Request $request){
        $mayusculas_sistema = Helpers::mayusculas_sistema();
        $numerobanco= $request->numero;
        //modificar registro
        $Banco = Banco::where('Numero', $numerobanco )->first();
        Banco::where('Numero', $numerobanco)
                    ->update([
                        'Nombre'=>$request->nombre,
                        'Cuenta'=>$request->cuenta,
                    ]);
        /*
        $Banco->Nombre=$request->nombre;   
        $Banco->Cuenta=$request->cuenta;
        */ 
        Log::channel('banco')->info('Se modifico el banco: '.$Banco.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		//$Banco->save();
    	return response()->json($Banco); 
    }
    //exportar a excel
    public function bancos_exportar_excel(){
        return Excel::download(new BancosExport, 'bancos.xlsx');
    }
}
