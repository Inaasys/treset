<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsignacionHerramientaExport;
use App\Configuracion_Tabla;
use App\Asignacion_Herramienta;
use App\Asignacion_Herramienta_Detalle;
use App\Personal;
use App\BitacoraDocumento;
use App\VistaAsignacionHerramienta;
use App\Producto;
use App\Existencia;

class AsignacionHerramientaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'asignacion_herramientas')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }
    public function asignacionherramienta(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'AsignacionHerramienta');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('asignacion_herramienta_guardar_configuracion_tabla');
        $rutacreardocumento = route('asignacion_herramienta_generar_pdfs');
        return view('registros.asignacionherramienta.asignacionherramienta', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','rutacreardocumento'));
    }
    //obtener las asignaciones de herramienta
    public function asignacion_herramienta_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaAsignacionHerramienta::select($this->campos_consulta)->orderBy('id', 'DESC')->where('periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->asignacion .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonautorizar =  '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Autorizar" onclick="autorizarasignacion(\''.$data->asignacion .'\')"><i class="material-icons">check</i></div> ';
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->asignacion .'\')"><i class="material-icons">cancel</i></div> ';
                        if($data->status == 'BAJA'){
                            $operaciones = '';
                        }else{
                            if($data->autorizado_por == ''){
                                $operaciones =  $botoncambios.$botonautorizar.$botonbajas;
                            }else{
                                $operaciones =  $botonbajas;
                            }
                        }
                        return $operaciones;
                    })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener id
    public function asignacion_herramienta_obtener_ultimo_id(){
        $id = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta');
        return response()->json($id);
    }
    //obtener personal que recibe herramienta
    public function asignacion_herramienta_obtener_personal_recibe(Request $request){
        if($request->ajax()){
            $data = Personal::where('status', 'ALTA')->orderBy("id", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpersonalrecibe('.$data->id.',\''.$data->nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener personal que entrega herramienta
    public function asignacion_herramienta_obtener_personal_entrega(Request $request){
        if($request->ajax()){
            $data = Personal::where('status', 'ALTA')->where('id', '<>', $request->numeropersonalrecibe)->orderBy("id", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpersonalentrega('.$data->id.',\''.$data->nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener herramienta
    public function asignacion_herramienta_obtener_herramienta(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from Existencias group by codigo) as e"),
            function($join){
                $join->on("e.codigo","=","t.codigo");
            })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Unidad as Unidad', 'e.Existencias as Existencias', 't.Costo as Costo')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '';
                        if($data->Existencias > 0){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaherramienta(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\')">Seleccionar</div>';
                        }
                        return $boton;
                    })
                    ->addColumn('Costo', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('Existencias', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Existencias);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //guardar regustro
    public function asignacion_herramienta_guardar(Request $request){
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas ON');
        $id = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $asignacion = $id.'-'.$request->serie;
		$Asignacion_Herramienta = new Asignacion_Herramienta;
		$Asignacion_Herramienta->id=$id;
        $Asignacion_Herramienta->asignacion=$asignacion;
		$Asignacion_Herramienta->serie=$request->serie;
        $Asignacion_Herramienta->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Asignacion_Herramienta->recibe_herramienta=$request->numeropersonalrecibe;
		$Asignacion_Herramienta->entrega_herramienta=$request->numeropersonalentrega;
		$Asignacion_Herramienta->total=$request->total;
        $Asignacion_Herramienta->observaciones=$request->observaciones;
        $Asignacion_Herramienta->status="ALTA";
        //$Asignacion_Herramienta->equipo=$request->equipo;
        $Asignacion_Herramienta->usuario=Auth::user()->user;
        $Asignacion_Herramienta->periodo=$request->periodohoy;
        $Asignacion_Herramienta->save();
        DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas OFF');
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ASIGNACION DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $asignacion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        //$BitacoraDocumento->Equipo = $request->equipo;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){   
            DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles ON');
            $iddetalle = Helpers::ultimoidregistrotabla('App\Asignacion_Herramienta_Detalle');          
            $Asignacion_Herramienta_Detalle=new Asignacion_Herramienta_Detalle;
            $Asignacion_Herramienta_Detalle->id = $iddetalle;
            $Asignacion_Herramienta_Detalle->id_asignacion_herramienta = $id;
            $Asignacion_Herramienta_Detalle->asignacion = $asignacion;
            $Asignacion_Herramienta_Detalle->fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $Asignacion_Herramienta_Detalle->herramienta = $codigoproductopartida;
            $Asignacion_Herramienta_Detalle->descripcion = $request->nombreproductopartida [$key];
            $Asignacion_Herramienta_Detalle->unidad = $request->unidadproductopartida [$key];
            $Asignacion_Herramienta_Detalle->cantidad =  $request->cantidadpartida  [$key];
            $Asignacion_Herramienta_Detalle->precio =  $request->preciopartida [$key];
            $Asignacion_Herramienta_Detalle->total = $request->totalpesospartida [$key];
            $Asignacion_Herramienta_Detalle->estado_herramienta = $request->estadopartida  [$key];
            $Asignacion_Herramienta_Detalle->item = $item;
            $Asignacion_Herramienta_Detalle->save();
            $item++;
            DB::unprepared('SET IDENTITY_INSERT asignacion_herramientas_detalles OFF');
        }
    	return response()->json($Asignacion_Herramienta);
    }
    //buscar string like id
    public function asignacion_herramienta_buscar_id_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaAsignacionHerramienta::where('asignacion', 'like', '%' . $string . '%')->orderBy('id', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->asignacion .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Total', function($data){
                    return Helpers::convertirvalorcorrecto($data->total);
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        } 
    }
    //generar documento
    public function asignacion_herramienta_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $asignacionesherramientas = VistaAsignacionHerramienta::whereIn('asignacion', $request->arraypdf)->orderBy('id', 'ASC')->take(500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $asignacionesherramientas = VistaAsignacionHerramienta::whereBetween('fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('id', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($asignacionesherramientas as $ah){
            $asignacionherramientadetalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            $datadetalle=array();
            foreach($asignacionherramientadetalle as $ahd){
                $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad),
                    "herramientadetalle"=>$ahd->herramienta,
                    "descripciondetalle"=>$ahd->descripcion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($ahd->total),
                    "estadodetalle" => $ahd->estado_herramienta
                );
            } 
            $data[]=array(
                      "asignacion"=>$ah,
                      "totalasignacion"=>Helpers::convertirvalorcorrecto($ah->total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle
            );
        }
        $pdf = PDF::loadView('registros.asignacionherramienta.formato_pdf_asignacion_herramienta', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }
    //guardar configuracion tabla
    public function asignacion_herramienta_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'asignacion_herramientas')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('asignacionherramienta');
    }
    //exportar excel
    public function asignacion_herramienta_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new AsignacionHerramientaExport($this->campos_consulta), "asignacionherramienta.xlsx"); 
    }
    //autorizar asignacion herramienta
    public function asignacion_herramienta_autorizar(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignacionautorizar)->first();
        $Asignacion_Herramienta->autorizado_por = Auth::user()->user; 
        $Asignacion_Herramienta->fecha_autorizacion = Helpers::fecha_exacta_accion_datetimestring();
        $Asignacion_Herramienta->save();
        //restar la cantidad asignada al codigo en la tabla de existencias
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionautorizar)->get();
        foreach($Asignacion_Herramienta_Detalle as $detalle){
            $Existencia = Existencia::where('Codigo', $detalle->herramienta)->first();
            $Existencia->Existencias = $Existencia->Existencias - $detalle->cantidad;
            $Existencia->save();
        }
        return response()->json($Asignacion_Herramienta);
    }
    //obtener datos asignacion herramienta seleccionada
    public function asignacion_herramienta_obtener_asignacion_herramienta(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignacionmodificar)->first();
        $personalrecibe = Personal::where('id', $Asignacion_Herramienta->recibe_herramienta)->first();
        $personalentrega = Personal::where('id', $Asignacion_Herramienta->entrega_herramienta)->first();
        //detalles orden compra
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionmodificar)->get();
        $Numero_Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignacionmodificar)->count();
        if($Numero_Asignacion_Herramienta_Detalle > 0){
            $filasdetallesasignacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($Asignacion_Herramienta_Detalle as $ahd){
                $existencias = DB::table('Productos as t')
                ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
                ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from Existencias group by codigo) as e"),
                function($join){
                    $join->on("e.codigo","=","t.codigo");
                })
                ->select('e.Existencias as Existencias')
                ->where('t.Codigo', $ahd->herramienta)
                ->get();
                if($ahd->estado_herramienta == 'Nuevo'){
                    $opciones = '<option value="Nuevo" selected>Nuevo</option>'.
                                '<option value="Usado">Usado</option>';
                }else{
                    $opciones = '<option value="Nuevo">Nuevo</option>'.
                    '<option value="Usado" selected>Usado</option>';
                }
                $filasdetallesasignacion= $filasdetallesasignacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$ahd->herramienta.'" readonly>'.$ahd->herramienta.'</td>'.
                    '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'.$ahd->descripcion.'" readonly>'.$ahd->descripcion.'</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$ahd->unidad.'" readonly>'.$ahd->unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($existencias[0]->Existencias).'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->precio).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->total).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod">'.
                    '<select name="estadopartida[]" class="form-control" style="width:100% !important;height: 28px !important;" required>'.
                        '<option selected disabled hidden>Selecciona</option>'.
                        $opciones.
                    '</select>'.
                    '</td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesasignacion = '';
        }        
        $data = array(
            "Asignacion_Herramienta" => $Asignacion_Herramienta,
            "filasdetallesasignacion" => $filasdetallesasignacion,
            "Numero_Asignacion_Herramienta_Detalle" => $Numero_Asignacion_Herramienta_Detalle,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => 1,
            "fecha" => Helpers::formatoinputdate($Asignacion_Herramienta->fecha),
            "total" => Helpers::convertirvalorcorrecto($Asignacion_Herramienta->total),
            "personalrecibe" => $personalrecibe,
            "personalentrega" => $personalentrega
        );
        return response()->json($data);
    }
    //guardar cambios de la asignacion
    public function asignacion_herramienta_guardar_modificacion(Request $request){
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $asignacion = $request->id.'-'.$request->serie;
        $Asignacion_Herramienta = Asignacion_Herramienta::where('id', $request->id)->first();
        $Asignacion_Herramienta->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Asignacion_Herramienta->recibe_herramienta=$request->numeropersonalrecibe;
		$Asignacion_Herramienta->entrega_herramienta=$request->numeropersonalentrega;
		$Asignacion_Herramienta->total=$request->total;
        $Asignacion_Herramienta->observaciones=$request->observaciones;
        $Asignacion_Herramienta->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ASIGNACION DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $asignacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Asignacion_Herramienta->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){   
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('id_asignacion_herramienta', $request->id)->first();
            $Asignacion_Herramienta_Detalle->cantidad =  $request->cantidadpartida  [$key];
            $Asignacion_Herramienta_Detalle->precio =  $request->preciopartida [$key];
            $Asignacion_Herramienta_Detalle->total = $request->totalpesospartida [$key];
            $Asignacion_Herramienta_Detalle->estado_herramienta = $request->estadopartida  [$key];
            $Asignacion_Herramienta_Detalle->save();
        }
    	return response()->json($Asignacion_Herramienta);
    }
    //dar de baja asignacion de herramienta
    public function asignacion_herramienta_alta_o_baja(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('asignacion', $request->asignaciondesactivar)->first();
        $Asignacion_Herramienta->motivo_baja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $Asignacion_Herramienta->status = 'BAJA';
        $Asignacion_Herramienta->save();
        //regresar cantidad asignada al codigo en la tabla existencias
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $request->asignaciondesactivar)->get();
        foreach($Asignacion_Herramienta_Detalle as $detalle){
            $Existencia = Existencia::where('Codigo', $detalle->herramienta)->first();
            $Existencia->Existencias = $Existencia->Existencias + $detalle->cantidad;
            $Existencia->save();
        }
        return response()->json($Asignacion_Herramienta);
    }
    //obtener personal para crear excel
    public function asignacion_herramienta_generar_excel_obtener_personal(){
        $personal = Personal::where('status', 'ALTA')->get();
        return response()->json($personal);
    }
    //obtener toda la herramienta asignada al personal seleccionado
    public function asignacion_herramienta_obtener_herramienta_personal(Request $request){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $request->idpersonal)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
        $filasdetallesasignacion = '';
        $contadorfilas = 0;
        foreach($Asignacion_Herramienta as $ah){
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            foreach($Asignacion_Herramienta_Detalle as $ahd){    
                if($ahd->estado_auditoria == 'FALTANTE'){
                    $opciones = '<option value="FALTANTE" selected>FALTANTE</option>'.
                                '<option value="OK">OK</option>';
                }elseif($ahd->estado_auditoria == 'OK'){
                    $opciones = '<option value="FALTANTE">FALTANTE</option>'.
                                '<option value="OK" selected>OK</option>';
                }else{
                    $opciones = '<option value="FALTANTE">FALTANTE</option>'.
                                '<option value="OK">OK</option>';
                }
                $filasdetallesasignacion= $filasdetallesasignacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><input type="hidden" class="form-control asignacionpartida" name="asignacionpartida[]" id="asignacsionpartida[]" value="'.$ahd->id.'" readonly>'.$ahd->asignacion.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$ahd->herramienta.'" readonly>'.$ahd->herramienta.'</td>'.
                    '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" id="nombreproductopartida[]" value="'.$ahd->descripcion.'" readonly>'.$ahd->descripcion.'</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" id="unidadproductopartida[]" value="'.$ahd->unidad.'" readonly>'.$ahd->unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" onchange="formatocorrectoinputcantidades(this)" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->precio).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" id="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->total).'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod">'.
                        '<select name="estadoauditoria[]" class="form-control" style="width:100% !important;height: 28px !important;" required>'.
                            '<option selected disabled hidden>Selecciona</option>'.
                            $opciones.
                        '</select>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadauditoriapartida" name="cantidadauditoriapartida[]" id="cantidadauditoriapartida[]" value="'.Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($ahd->cantidad).'" onchange="formatocorrectoinputcantidades(this)" required></td>'.
                '</tr>';
                $contadorfilas++;
            }
        }
        //url para generar reporte de auditoria
        $urlgenerarreporteauditoria  = route('asignacion_herramienta_generar_reporte_auditoria', $request->idpersonal);
        $data = array(
            "filasdetallesasignacion" => $filasdetallesasignacion,
            "contadorfilas" => $contadorfilas,
            "urlgenerarreporteauditoria" => $urlgenerarreporteauditoria
        );
        return response()->json($data);
    }
    //guardar auditoria
    public function asignacion_herramienta_guardar_auditoria(Request $request){
        foreach ($request->asignacionpartida as $key => $iddetalleasignacion){   
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('id', $iddetalleasignacion)->first();
            $Asignacion_Herramienta_Detalle->estado_auditoria =  $request->estadoauditoria  [$key];
            $Asignacion_Herramienta_Detalle->cantidad_auditoria =  $request->cantidadauditoriapartida [$key];
            $Asignacion_Herramienta_Detalle->save();
        }
    	return response()->json($Asignacion_Herramienta_Detalle);
    }
    //generar reporte de auditoria
    public function asignacion_herramienta_generar_reporte_auditoria($id){
        $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $id)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
        $Personal_Recibe_Herramienta = Personal::where('id', $id)->first();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $totalasignacion = 0;
        $data=array();
        $datadetalle=array();

        foreach ($Asignacion_Herramienta as $ah){
            $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
            foreach($Asignacion_Herramienta_Detalle as $ahd){
                if($ahd->estado_auditoria == 'FALTANTE'){
                    $producto = Producto::where('Codigo', $ahd->herramienta)->first();
                    $totaldetalle = $ahd->cantidad_auditoria * $ahd->precio;
                    $datadetalle[]=array(
                        "cantidadauditoriadetalle"=> Helpers::convertirvalorcorrecto($ahd->cantidad_auditoria),
                        "herramientadetalle"=>$ahd->herramienta,
                        "descripciondetalle"=>$ahd->descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($ahd->precio),
                        "totaldetalle" => Helpers::convertirvalorcorrecto($totaldetalle),
                        "estadoauditoriadetalle" => $ahd->estado_auditoria,
                        "asignaciondetalle" => $ahd->asignacion
                    );
                    $totalasignacion = $totalasignacion + $totaldetalle;
                }
            } 

        }
        $data[]=array(
            "asignacion"=>$ah,
            "totalasignacion"=>Helpers::convertirvalorcorrecto($totalasignacion),
            "fechaformato"=> $fechaformato,
            "datadetalle" => $datadetalle,
            "Personal_Recibe_Herramienta" => $Personal_Recibe_Herramienta
        );
        $pdf = PDF::loadView('registros.asignacionherramienta.formato_pdf_asignacion_herramienta_auditoria', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }
}
