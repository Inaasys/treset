<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AlmacenesExport;
use App\Almacen;

class AlmacenController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function almacenes(){
        return view('catalogos.almacenes.almacenes');
    }
    //obtener todos los registros
    public function almacenes_obtener(Request $request){
        if($request->ajax()){
            $data = Almacen::orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
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
                        /*if($data->Status == 'ALTA'){
                            $operaciones =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $operaciones = '';
                            //$operaciones = '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Numero.')">Altas</div>';
                        } */
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
    public function almacenes_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Almacen');
        return response()->json($id);
    } 
    //guardar en catalogo
    public function almacenes_guardar(Request $request){
        //obtener el ultimo id de la tabla
        $id = Helpers::ultimoidtabla('App\Almacen');
		$Almacen = new Almacen;
		$Almacen->Numero=$id;
		$Almacen->Nombre=$request->nombre;
        $Almacen->Status='ALTA';
        Log::channel('almacen')->info('Se registro un nuevo almacen: '.$Almacen.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Almacen->save();
        return response()->json($Almacen); 
    } 
    //dar de baja o alta en catalogo
    public function almacenes_alta_o_baja(Request $request){
        $numeroalmacen=$request->numeroalmacen;
	    $Almacen = Almacen::where('Numero', $numeroalmacen )->first();
	    if($Almacen->Status == 'ALTA'){
	       $Almacen->Status = 'BAJA';
           Log::channel('almacen')->info('El almacen fue dado de baja: '.$Almacen.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Almacen->Status = 'ALTA';
           Log::channel('almacen')->info('El almacen fue dado de alta: '.$Almacen.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Almacen->save();
	    return response()->json($Almacen);
    } 
    //obtener datos del catalogo
    public function almacenes_obtener_almacen(Request $request){
        $almacen = Almacen::where('Numero', $request->numeroalmacen)->first();
        $data = array(
            "almacen" => $almacen
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function almacenes_guardar_modificacion(Request $request){
        $numeroalmacen= $request->numero;
        //modificar registro
        $Almacen = Almacen::where('Numero', $numeroalmacen )->first();
        $Almacen->Nombre=$request->nombre;    
        Log::channel('almacen')->info('Se modifico el almacen: '.$Almacen.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		$Almacen->save();
    	return response()->json($Almacen); 
    }  
    //exportar a excel
    public function almacenes_exportar_excel(){
        return Excel::download(new AlmacenesExport, 'almacenes.xlsx');
    }
}
