<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LineasExport;
use App\Linea;

class LineaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function lineas(){
        return view('catalogos.lineas.lineas');
    }
    //obtener todos los registros
    public function lineas_obtener(Request $request){
        if($request->ajax()){
            $data = Linea::orderBy("Numero", "ASC")->get();
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
                            $operaciones =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios"  onclick="obtenerdatos('.$data->Numero.')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-red btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar('.$data->Numero.')"><i class="material-icons">cancel</i></div>';
                        }else{
                            $operaciones = '';
                            //$operaciones =    '<div class="btn bg-green btn-xs waves-effect" onclick="desactivar('.$data->Numero.')">Altas</div>';
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
    public function lineas_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Linea');
        return response()->json($id);
    } 
    //guardar en catalogo
    public function lineas_guardar(Request $request){
        //obtener el ultimo id de la tabla
        $id = Helpers::ultimoidtabla('App\Linea');
		$Linea = new Linea;
		$Linea->Numero=$id;
		$Linea->Nombre=$request->nombre;
        $Linea->Status='ALTA';
        Log::channel('linea')->info('Se registro una nueva linea: '.$Linea.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Linea->save();
        return response()->json($Linea); 
    } 
    //dar de baja o alta en catalogo
    public function lineas_alta_o_baja(Request $request){
        $numerolinea=$request->numerolinea;
	    $Linea = Linea::where('Numero', $numerolinea )->first();
	    if($Linea->Status == 'ALTA'){
	       $Linea->Status = 'BAJA';
           Log::channel('linea')->info('La linea fue dada de baja: '.$Linea.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Linea->Status = 'ALTA';
           Log::channel('linea')->info('La linea fue dada de alta: '.$Linea.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Linea->save();
	    return response()->json($Linea);
    } 
    //obtener datos del catalogo
    public function lineas_obtener_linea(Request $request){
        $linea = Linea::where('Numero', $request->numerolinea)->first();
        $data = array(
            "linea" => $linea
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function lineas_guardar_modificacion(Request $request){
        $numerolinea= $request->numero;
        //modificar registro
        $LineaAnterior = Linea::where('Numero', $numerolinea )->first();
        $Linea = Linea::where('Numero', $numerolinea )->first();
        $Linea->Nombre=$request->nombre;    
        Log::channel('linea')->info('Se modifico una linea, Linea Anterior: '.$LineaAnterior.' Linea Actualizada: '.$Linea.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		$Linea->save();
    	return response()->json($Linea); 
    }
    //exportar a excel
    public function lineas_exportar_excel(){
        return Excel::download(new LineasExport, 'lineas.xlsx');
    }
}
