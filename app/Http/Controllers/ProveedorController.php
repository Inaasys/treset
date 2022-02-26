<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProveedoresExport;
use App\Proveedor;
use App\CodigoPostal;
use App\Configuracion_Tabla;
use App\VistaProveedor;

class ProveedorController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones en el controlador ConfiguracionSistemaController
    }

    public function proveedores(){
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Proveedores', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('proveedores_guardar_configuracion_tabla');
        return view('catalogos.proveedores.proveedores', compact('configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener todos los registros
    public function proveedores_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Proveedores', Auth::user()->id);
            $data = VistaProveedor::select($configuraciones_tabla['campos_consulta']);
            return DataTables::of($data)
                    ->order(function ($query) use($configuraciones_tabla) {
                        if($configuraciones_tabla['configuracion_tabla']->primerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->primerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formaprimerordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->segundoordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->segundoordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formasegundoordenamiento . '');
                        }
                        if($configuraciones_tabla['configuracion_tabla']->tercerordenamiento != 'omitir'){
                            $query->orderBy($configuraciones_tabla['configuracion_tabla']->tercerordenamiento, '' . $configuraciones_tabla['configuracion_tabla']->formatercerordenamiento . '');
                        }
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
                    ->setRowClass(function ($data) {
                        return $data->Status == 'ALTA' ? '' : 'bg-orange';
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }   
    //obtener codigo
    public function proveedores_buscar_rfc_en_tabla(Request $request){
        $existerfc = Proveedor::where('Rfc', $request->rfc)->count();
        return response()->json($existerfc);
    }
    //obtener ultimo numero de tabla
    public function proveedores_obtener_ultimo_numero(){
        $id = Helpers::ultimoidtabla('App\Proveedor');
        return response()->json($id);
    } 
    //obtener codigos postales
    public function proveedores_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcodigopostal(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }        
    } 
    //guardar en catalogo
    public function proveedores_guardar(Request $request){
            //obtener el ultimo id de la tabla
            $id = Helpers::ultimoidtabla('App\Proveedor');
		    $Proveedor = new Proveedor;
		    $Proveedor->Numero=$id;
		    $Proveedor->Nombre=$request->nombre;
		    $Proveedor->Rfc=$request->rfc;
            $Proveedor->CodigoPostal=$request->codigopostal;
            $Proveedor->Email1=$request->email1;
            $Proveedor->Email2=$request->email2;
            $Proveedor->Email3=$request->email3;
            $Proveedor->Plazo=$request->plazo;
            $Proveedor->Telefonos=$request->telefonos;
            $Proveedor->Status='ALTA';
            $Proveedor->SolicitarXML = 1;
            Log::channel('proveedor')->info('Se registro un nuevo proveedor: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
		    $Proveedor->save();
      	
        return response()->json($Proveedor); 
    } 
    //dar de baja o alta en catalogo
    public function proveedores_alta_o_baja(Request $request){
        $numeroproveedor=$request->numeroproveedor;
	    $Proveedor = Proveedor::where('Numero', $numeroproveedor )->first();
	    if($Proveedor->Status == 'ALTA'){
           $Proveedor->Status = 'BAJA';
           Log::channel('proveedor')->info('El proveedor fue dado de baja: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
	    }else{
	       $Proveedor->Status = 'ALTA';
           Log::channel('proveedor')->info('El proveedor fue dado de alta: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());
        }
	    $Proveedor->save();
	    return response()->json($Proveedor);
    } 
    //obtener datos del catalogo
    public function proveedores_obtener_proveedor(Request $request){
        $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->first();
        $data = array(
            "proveedor" => $proveedor
        );
        return response()->json($data);
    }  
    //modificar en catalogo
    public function proveedores_guardar_modificacion(Request $request){
        $numeroproveedor= $request->numero;
            //modificar registro
            $Proveedor = Proveedor::where('Numero', $numeroproveedor )->first();
            Proveedor::where('Numero', $numeroproveedor)
            ->update([
                'Nombre'=>$request->nombre,
                'Rfc'=>$request->rfc,
                'CodigoPostal'=>$request->codigopostal,
                'Email1'=>$request->email1,
                'Email2'=>$request->email2,
                'Email3'=>$request->email3,
                'Plazo'=>$request->plazo,
                'Telefonos'=>$request->telefonos	
            ]);
            //si es admin modificar la casilla solicitar xml	      
            if(Auth::user()->role_id == 1){
                Proveedor::where('Numero', $numeroproveedor)
                        ->update([
                            'SolicitarXML' => $request->solicitarxmlencompras,
                        ]);
            }
            /*
		    $Proveedor->Nombre=$request->nombre;
		    $Proveedor->Rfc=$request->rfc;
            $Proveedor->CodigoPostal=$request->codigopostal;
            $Proveedor->Email1=$request->email1;
            $Proveedor->Email2=$request->email2;
            $Proveedor->Email3=$request->email3;
            $Proveedor->Plazo=$request->plazo;
            $Proveedor->Telefonos=$request->telefonos;	  
            if(Auth::user()->role_id == 1){
                $Proveedor->SolicitarXML = $request->solicitarxmlencompras;
            }
            */
            Log::channel('proveedor')->info('Se modifico el proveedor: '.$Proveedor.' Por el empleado: '.Auth::user()->name.' correo: '.Auth::user()->email.' El: '.Helpers::fecha_exacta_accion());	      
		    //$Proveedor->save();
    	return response()->json($Proveedor); 
    } 
    //exportar a excel
    public function proveedores_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Proveedores', Auth::user()->id);
        return Excel::download(new ProveedoresExport($configuraciones_tabla['campos_consulta']), "proveedores.xlsx");  
    }  
    //guardar configuracion tabla
    public function proveedores_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Proveedores', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Proveedores')->where('IdUsuario', Auth::user()->id)
            ->update([
                'campos_activados' => $request->string_datos_tabla_true,
                'campos_desactivados' => $string_datos_tabla_false,
                'columnas_ordenadas' => $request->string_datos_ordenamiento_columnas,
                'usuario' => Auth::user()->user,
                'primerordenamiento' => $request->selectorderby1,
                'formaprimerordenamiento' => $request->deorderby1,
                'segundoordenamiento' => $request->selectorderby2,
                'formasegundoordenamiento' => $request->deorderby2,
                'tercerordenamiento' => $request->selectorderby3,
                'formatercerordenamiento' => $request->deorderby3,
                'campos_busquedas' => substr($selectmultiple, 1),
            ]);
        }else{
            $Configuracion_Tabla=new Configuracion_Tabla;
            $Configuracion_Tabla->tabla='Proveedores';
            $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
            $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
            $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
            $Configuracion_Tabla->ordenar = 0;
            $Configuracion_Tabla->usuario = Auth::user()->user;
            $Configuracion_Tabla->campos_busquedas = substr($selectmultiple, 1);
            $Configuracion_Tabla->primerordenamiento = $request->selectorderby1;
            $Configuracion_Tabla->formaprimerordenamiento = $request->deorderby1;
            $Configuracion_Tabla->segundoordenamiento =  $request->selectorderby2;
            $Configuracion_Tabla->formasegundoordenamiento =  $request->deorderby2;
            $Configuracion_Tabla->tercerordenamiento = $request->selectorderby3;
            $Configuracion_Tabla->formatercerordenamiento = $request->deorderby3;
            $Configuracion_Tabla->IdUsuario = Auth::user()->id;
            $Configuracion_Tabla->save();
        }
        return redirect()->route('proveedores');
    }  
}
