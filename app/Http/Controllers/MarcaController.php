<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use App\Marca;

class MarcaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function marcas(){
        return view('catalogos.marcas.marcas');
    }
    //obtener todos los registros
    public function marcas_obtener(Request $request){
        if($request->ajax()){
            $data = Marca::orderBy("Numero", "ASC")->get();
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
                    ->addColumn('Utilidad1', function($data){
                        $utilidad1 = Helpers::convertirvalorcorrecto($data->Utilidad1);
                        return $utilidad1;
                    })
                    ->addColumn('Utilidad2', function($data){
                        $utilidad2 = Helpers::convertirvalorcorrecto($data->Utilidad2);
                        return $utilidad2;
                    })
                    ->addColumn('Utilidad3', function($data){
                        $utilidad3 = Helpers::convertirvalorcorrecto($data->Utilidad3);
                        return $utilidad3;
                    })
                    ->addColumn('Utilidad4', function($data){
                        $utilidad4 = Helpers::convertirvalorcorrecto($data->Utilidad4);
                        return $utilidad4;
                    })
                    ->addColumn('Utilidad5', function($data){
                        $utilidad5 = Helpers::convertirvalorcorrecto($data->Utilidad5);
                        return $utilidad5;
                    })
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones','Utilidad1','Utilidad2','Utilidad3','Utilidad4','Utilidad5'])
                    ->make(true);
        } 
    }  
    //obtener ultimo numero de tabla
    public function marcas_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Marca');
        return response()->json($id);
    } 
    //guardar en catalogo
    public function marcas_guardar(Request $request){
        //obtener el ultimo id de la tabla
        $id = Helpers::ultimoidtabla('App\Marca');
		$Marca = new Marca;
		$Marca->Numero=$id;
        $Marca->Nombre=$request->nombre;
        $Marca->Utilidad1=$request->utilidad1;
        $Marca->Utilidad2=$request->utilidad2;
        $Marca->Utilidad3=$request->utilidad3;
        $Marca->Utilidad4=$request->utilidad4;
        $Marca->Utilidad5=$request->utilidad5;
        $Marca->Status='ALTA';
        Log::channel('marca')->info('Se registro una nueva marca: '.$Marca.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        $Marca->save();
        return response()->json($Marca); 
    } 
    //dar de baja o alta en catalogo
    public function marcas_alta_o_baja(Request $request){
        $numeromarca=$request->numeromarca;
	    $Marca = Marca::where('Numero', $numeromarca )->first();
	    if($Marca->Status == 'ALTA'){
	       $Marca->Status = 'BAJA';
           Log::channel('marca')->info('La marca fue dada de baja: '.$Marca.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }else{
	       $Marca->Status = 'ALTA';
           Log::channel('marca')->info('La marca fue dada de alta: '.$Marca.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Marca->save();
	    return response()->json($Marca);
    } 
    //obtener datos del catalogo
    public function marcas_obtener_marca(Request $request){
        $marca = Marca::where('Numero', $request->numeromarca)->first();
        $data = array(
            "marca" => $marca,
            "utilidad1" => Helpers::convertirvalorcorrecto($marca->Utilidad1),
            "utilidad2" => Helpers::convertirvalorcorrecto($marca->Utilidad2),
            "utilidad3" => Helpers::convertirvalorcorrecto($marca->Utilidad3),
            "utilidad4" => Helpers::convertirvalorcorrecto($marca->Utilidad4),
            "utilidad5" => Helpers::convertirvalorcorrecto($marca->Utilidad5)
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function marcas_guardar_modificacion(Request $request){
        $numeromarca= $request->numero;
        //modificar registro
        $Marca = Marca::where('Numero', $numeromarca )->first();
        $Marca->Nombre=$request->nombre;
        $Marca->Utilidad1=$request->utilidad1;
        $Marca->Utilidad2=$request->utilidad2;
        $Marca->Utilidad3=$request->utilidad3;
        $Marca->Utilidad4=$request->utilidad4;
        $Marca->Utilidad5=$request->utilidad5;    
        Log::channel('marca')->info('Se modifico la marca: '.$Marca.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		$Marca->save();
    	return response()->json($Marca); 
    }
}
