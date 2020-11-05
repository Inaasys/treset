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
use App\Exports\PrestamoHerramientaExport;
use App\Configuracion_Tabla;
use App\Asignacion_Herramienta;
use App\Asignacion_Herramienta_Detalle;
use App\Prestamo_Herramienta;
use App\Prestamo_Herramienta_Detalle;
use App\Personal;
use App\BitacoraDocumento;
use App\VistaPrestamoHerramienta;
use App\Producto;
use Mail;

class PrestamoHerramientaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'prestamo_herramientas')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }   

    public function prestamoherramienta(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'PrestamoHerramienta');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('prestamo_herramienta_guardar_configuracion_tabla');
        return view('registros.prestamoherramienta.prestamoherramienta', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla'));
    }
    //obtener prestamos de herramienta
    public function prestamo_herramienta_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaPrestamoHerramienta::select($this->campos_consulta)->orderBy('id', 'DESC')->where('periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->prestamo .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonterminarprestamo=      '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Terminar Prestamo" onclick="terminarprestamo(\''.$data->prestamo .'\')"><i class="material-icons">check</i></div> ';
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->prestamo .'\')"><i class="material-icons">cancel</i></div> ';
                        if($data->status == 'BAJA'){
                            $operaciones = '';
                        }else if($data->status == 'ENTREGADO'){
                            $operaciones = '';
                        }else{
                                $operaciones =  $botonbajas.$botoncambios.$botonterminarprestamo;
                        }
                        return $operaciones;
                    })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener ultimo id
    public function prestamo_herramienta_obtener_ultimo_id(){
        $id = Helpers::ultimoidregistrotabla('App\Prestamo_Herramienta');
        return response()->json($id);
    }
    //obtener personla
    public function prestamo_herramienta_obtener_personal(){
        $personal = Personal::where('status', 'ALTA')->get();
        return response()->json($personal);
    }
    //obtener herramienta asignada al personal seleccionado
    public function prestamo_herramienta_obtener_herramienta_personal(Request $request){
        if($request->ajax()){
            $data = array();
            $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $request->personalherramientacomun)->where('status', 'ALTA')->where('autorizado_por', '<>', '')->get();
            foreach($Asignacion_Herramienta as $ah){
                $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('asignacion', $ah->asignacion)->get();
                foreach($Asignacion_Herramienta_Detalle as $ahd){    
                    $data[]=array(
                        "id_detalle_asignacion"=>$ahd->id,
                        "asignacion"=>$ahd->asignacion,
                        "herramienta"=>$ahd->herramienta,
                        "descripcion"=>$ahd->descripcion,
                        "unidad"=>$ahd->unidad,
                        "cantidad"=>$ahd->cantidad,
                        "precio"=>$ahd->precio,
                        "total"=>$ahd->total
                    );
                }
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarherramientaasignada('.$data['id_detalle_asignacion'].')">Prestar</div>';
                        return $boton;
                    })
                    ->addColumn('cantidad', function($data){return Helpers::convertirvalorcorrecto($data['cantidad']);})
                    ->addColumn('precio', function($data){return Helpers::convertirvalorcorrecto($data['precio']);})
                    ->addColumn('total', function($data){return Helpers::convertirvalorcorrecto($data['total']);})
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener fecha datetimelocal
    public function prestamo_herramienta_obtener_fecha_datetimelocal(){
        $fechahoy = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechahoy);
    }
    //obtener detalle de asignacion seleccionada
    public function prestamo_herramienta_obtener_detalle_asignacion_seleccionada(Request $request){
        $Asignacion_Herramienta_Detalle = Asignacion_Herramienta_Detalle::where('id', $request->iddetalleasignacionherramienta)->first();
        $Prestamos_Herramienta_Seleccionada = Prestamo_Herramienta_Detalle::where('id_detalle_asignacion_herramienta', $request->iddetalleasignacionherramienta)->where('herramienta', $Asignacion_Herramienta_Detalle->herramienta)->where('status_prestamo', 'PRESTADO')->get()->sum('cantidad');
        if($Asignacion_Herramienta_Detalle->estado_herramienta == 'Nuevo'){
            $opciones = '<option value="Nuevo" selected>Nuevo</option>'.
                        '<option value="Usado">Usado</option>';
        }else{
            $opciones = '<option value="Nuevo">Nuevo</option>'.
            '<option value="Usado" selected>Usado</option>';
        }
        $data = array(
            'herramienta' => $Asignacion_Herramienta_Detalle->herramienta,
            'descripcion' => $Asignacion_Herramienta_Detalle->descripcion,
            'unidad' => $Asignacion_Herramienta_Detalle->unidad,
            'cantidad' => Helpers::convertirvalorcorrecto($Asignacion_Herramienta_Detalle->cantidad - $Prestamos_Herramienta_Seleccionada),
            'precio' => Helpers::convertirvalorcorrecto($Asignacion_Herramienta_Detalle->precio),
            'total' => Helpers::convertirvalorcorrecto($Asignacion_Herramienta_Detalle->total),
            'estado_herramienta' => $opciones,
            'fechahoy' => Helpers::fecha_exacta_accion_datetimelocal(),
            "Prestamos_Herramienta_Seleccionada" => $Prestamos_Herramienta_Seleccionada
        );
        return response()->json($data);
    }
    //obtener personal recibe
    public function prestamo_herramienta_obtener_personal_recibe(Request $request){
        if($request->ajax()){
            $data = Personal::where('status', 'ALTA')->where('id', '<>', $request->personalherramientacomun)->orderBy("id", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarpersonalrecibe('.$data->id.',\''.$data->nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //guardar prestamo herramienta
    public function prestamo_herramienta_guardar(Request $request){
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT prestamo_herramientas ON');
        $id = Helpers::ultimoidregistrotabla('App\Prestamo_Herramienta');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $prestamo = $id.'-'.$request->serie;
		$Prestamo_Herramienta = new Prestamo_Herramienta;
		$Prestamo_Herramienta->id=$id;
        $Prestamo_Herramienta->prestamo=$prestamo;
		$Prestamo_Herramienta->serie=$request->serie;
        $Prestamo_Herramienta->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Prestamo_Herramienta->recibe_herramienta=$request->numeropersonalrecibe;
		$Prestamo_Herramienta->entrega_herramienta=$request->personalherramientacomun;
		$Prestamo_Herramienta->total=$request->total;
        $Prestamo_Herramienta->observaciones=$request->observaciones;
        $Prestamo_Herramienta->correo=$request->correo;
        $Prestamo_Herramienta->status="ALTA";
        $Prestamo_Herramienta->inicio_prestamo = $request->inicioprestamo;
        $Prestamo_Herramienta->termino_prestamo = $request->terminoprestamo;
        $Prestamo_Herramienta->usuario=Auth::user()->user;
        $Prestamo_Herramienta->periodo=$request->periodohoy;
        $Prestamo_Herramienta->save();
        DB::unprepared('SET IDENTITY_INSERT prestamo_herramientas OFF');
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRESTAMO DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $prestamo;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        $filascorreo = array();
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){   
            DB::unprepared('SET IDENTITY_INSERT prestamo_herramientas_detalles ON');
            $iddetalle = Helpers::ultimoidregistrotabla('App\Prestamo_Herramienta_Detalle');          
            $Prestamo_Herramienta_Detalle=new Prestamo_Herramienta_Detalle;
            $Prestamo_Herramienta_Detalle->id = $iddetalle;
            $Prestamo_Herramienta_Detalle->id_prestamo_herramienta = $id;
            $Prestamo_Herramienta_Detalle->id_detalle_asignacion_herramienta = $request->iddetalleasignacionherramienta [$key];
            $Prestamo_Herramienta_Detalle->prestamo = $prestamo;
            $Prestamo_Herramienta_Detalle->fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $Prestamo_Herramienta_Detalle->herramienta = $codigoproductopartida;
            $Prestamo_Herramienta_Detalle->descripcion = $request->descripcionpartida [$key];
            $Prestamo_Herramienta_Detalle->unidad = $request->unidadpartida [$key];
            $Prestamo_Herramienta_Detalle->cantidad =  $request->cantidadpartida  [$key];
            $Prestamo_Herramienta_Detalle->precio =  $request->preciopartida [$key];
            $Prestamo_Herramienta_Detalle->total = $request->totalpartida [$key];
            $Prestamo_Herramienta_Detalle->estado_herramienta = $request->estadopartida  [$key];
            $Prestamo_Herramienta_Detalle->status_prestamo = "PRESTADO";
            $Prestamo_Herramienta_Detalle->item = $item;
            $Prestamo_Herramienta_Detalle->save();
            $item++;
            $filascorreo[]=array(
                "herramienta"=>$codigoproductopartida,
                "descripcionpartida"=>$request->descripcionpartida [$key],
                "cantidadpartida"=>$request->cantidadpartida  [$key]
            );
            DB::unprepared('SET IDENTITY_INSERT prestamo_herramientas_detalles OFF');
        }
        try{
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->correo;
            $correos = [$request->correo];
            $body = "Se realizo un prestamo de herramientas";
            $personal_entrega = Personal::where('id', $request->personalherramientacomun)->first();
            $personal_recibe = Personal::where('id', $request->numeropersonalrecibe)->first();
            $nombre_personal_entrega = $personal_entrega->nombre;
            $nombre_personal_recibe = $personal_recibe->nombre;
            $inicio_prestamo = Helpers::fecha_espanol_datetime($request->inicioprestamo);
            $termino_prestamo = Helpers::fecha_espanol_datetime($request->terminoprestamo);
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.prestamos.nuevoprestamoherramienta', compact('nombre_personal_entrega', 'nombre_personal_recibe', 'inicio_prestamo', 'termino_prestamo', 'body', 'receptor', 'horaaccionespanol','filascorreo'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Nuevo Prestamo de Herramientas');
            });
        } catch(\Exception $e) {
            $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
            $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
            $msj = 'Error al enviar correo nuevo prestamo de herramientas'.$prestamo;
            Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Error al enviar correo nuevo prestamo de herramientas');
            });
        }
    	return response()->json($Prestamo_Herramienta);
    }
    //dar de baja prestamo
    public function prestamo_herramienta_alta_o_baja(Request $request){
        //cambiar status del prestamo
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $request->prestamodesactivar)->first();
        $Prestamo_Herramienta->motivo_baja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $Prestamo_Herramienta->status = 'BAJA';
        $Prestamo_Herramienta->save();
        //cambiar status de los detalles
        $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamodesactivar)->get();
        foreach($Prestamo_Herramienta_Detalle as $phd){
            $Detalle_Prestamo = Prestamo_Herramienta_Detalle::where('id', $phd->id)->first();
            $Detalle_Prestamo->status_prestamo = 'BAJA';
            $Detalle_Prestamo->save();
        }
        return response()->json($Prestamo_Herramienta);
    }
    //terminar prestamo de herramienta
    public function prestamo_herramienta_terminar_prestamo(Request $request){
        //cambiar status del prestamo
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $request->prestamoterminarprestamo)->first();
        $Prestamo_Herramienta->status = 'ENTREGADO';
        $Prestamo_Herramienta->save();
        //cambiar status de los detalles
        $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamoterminarprestamo)->get();
        foreach($Prestamo_Herramienta_Detalle as $phd){
            $Detalle_Prestamo = Prestamo_Herramienta_Detalle::where('id', $phd->id)->first();
            $Detalle_Prestamo->status_prestamo = 'ENTREGADO';
            $Detalle_Prestamo->save();
        }
        try {
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $Prestamo_Herramienta->correo;
            $correos = [$Prestamo_Herramienta->correo];
            $body = "Se termino un prestamo de herramientas";
            $personal_entrega = Personal::where('id', $Prestamo_Herramienta->entrega_herramienta)->first();
            $personal_recibe = Personal::where('id', $Prestamo_Herramienta->recibe_herramienta)->first();
            $nombre_personal_entrega = $personal_entrega->nombre;
            $nombre_personal_recibe = $personal_recibe->nombre;
            $inicio_prestamo = Helpers::fecha_espanol_datetime($ph->inicio_prestamo);
            $termino_prestamo = Helpers::fecha_espanol_datetime($ph->termino_prestamo);
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            $filascorreo = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamoterminarprestamo)->get();
            Mail::send('correos.prestamos.prestamoterminado', compact('nombre_personal_entrega', 'nombre_personal_recibe', 'inicio_prestamo', 'termino_prestamo', 'body', 'receptor', 'horaaccionespanol','filascorreo'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Termino Prestamo de Herramientas');
            });
        } catch(\Exception $e) {
            $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
            $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
            $msj = 'Error al enviar correo termino prestamo de herramientas'.$Prestamo_Herramienta->prestamo;
            Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Error al enviar correo termino prestamo de herramientas');
            });
        }
        return response()->json($Prestamo_Herramienta);
    }
    //obtener prestamo a modificar
    public function prestamo_herramienta_obtener_prestamo_herramienta(Request $request){
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $request->prestamomodificar)->first();
        $personalrecibe = Personal::where('id', $Prestamo_Herramienta->recibe_herramienta)->first();
        $personalentrega = Personal::where('id', $Prestamo_Herramienta->entrega_herramienta)->first();
        //detalles
        $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamomodificar)->get();
        $Numero_Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamomodificar)->count();
        if($Numero_Prestamo_Herramienta_Detalle > 0){
            $filasdetallesprestamo = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($Prestamo_Herramienta_Detalle as $phd){
                if($phd->estado_herramienta == 'Nuevo'){
                    $opciones = '<option value="Nuevo" selected>Nuevo</option>'.
                                '<option value="Usado">Usado</option>';
                }else{
                    $opciones = '<option value="Nuevo">Nuevo</option>'.
                    '<option value="Usado" selected>Usado</option>';
                }
                $filasdetallesprestamo= $filasdetallesprestamo.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                            '<td class="tdmod"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control iddetalleasignacionherramienta" name="iddetalleasignacionherramienta[]" id="iddetalleasignacionherramienta[]" value="'.$phd->id_detalle_asignacion_herramienta.'" readonly><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" id="codigoproductopartida[]" value="'.$phd->herramienta.'" readonly>'.$phd->herramienta.'</td>'.
                            '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" id="descripcionpartida[]" value="'.$phd->descripcion.'" readonly>'.$phd->descripcion.'</div></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" id="unidadpartida[]" value="'.$phd->unidad.'" readonly>'.$phd->unidad.'</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" id="cantidadpartida[]" value="'.$phd->cantidad.'" data-parsley-min="0.1" data-parsley-max="'.$phd->cantidad.'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" required readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" id="preciopartida[]" value="'.$phd->precio.'" onchange="formatocorrectoinputcantidades(this);" required readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpartida" name="totalpartida[]" id="totalpartida[]" value="'.$phd->precio.'" onchange="formatocorrectoinputcantidades(this);" required readonly></td>'.
                            '<td class="tdmod">'.
                              '<select name="estadopartida[]" class="form-control" style="width:100% !important;height: 28px !important;" required readonly disabled>'.
                                  $opciones.
                              '</select>'.
                            '</td>'.  
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesprestamo = '';
        }  
        //select personal entrega
        $personal = Personal::where('status', 'ALTA')->get();
        $selectpersonal = "<option selected disabled hidden>Selecciona el personal</option>";
        foreach($personal as $p){
            if($p->id == $Prestamo_Herramienta->entrega_herramienta){
                $selectpersonal = $selectpersonal.'<option selected value='.$p->id.'>'.$p->nombre.' - '.$p->tipo_personal;
            }else{
                $selectpersonal = $selectpersonal.'<option value='.$p->id.'>'.$p->nombre.' - '.$p->tipo_personal;
            }
        }     
        $data = array(
            "Prestamo_Herramienta" => $Prestamo_Herramienta,
            "filasdetallesprestamo" => $filasdetallesprestamo,
            "Numero_Prestamo_Herramienta_Detalle" => $Numero_Prestamo_Herramienta_Detalle,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => 1,
            "fecha" => Helpers::formatoinputdate($Prestamo_Herramienta->fecha),
            "total" => Helpers::convertirvalorcorrecto($Prestamo_Herramienta->total),
            "personalrecibe" => $personalrecibe,
            "personalentrega" => $personalentrega,
            "selectpersonal" => $selectpersonal
        );
        return response()->json($data);
    }
    //guardar modificacion del prestamo
    public function prestamo_herramienta_guardar_modificacion(Request $request){
        //Modificar prestamo
        $prestamo = $request->id.'-'.$request->serie;
        $Prestamo_Herramienta = Prestamo_Herramienta::where('id', $request->id)->first();
        if($Prestamo_Herramienta->termino_prestamo < $request->terminoprestamo){
            $Prestamo_Herramienta->correo_enviado = NULL;
        }
        $Prestamo_Herramienta->termino_prestamo = $request->terminoprestamo;
        $Prestamo_Herramienta->correo=$request->correo;
        $Prestamo_Herramienta->observaciones=$request->observaciones;
        $Prestamo_Herramienta->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRESTAMO DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $prestamo;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Prestamo_Herramienta->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
    	return response()->json($Prestamo_Herramienta);
    }
    //exportar excel
    public function prestamo_herramienta_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new PrestamoHerramientaExport($this->campos_consulta), "prestamosherramienta.xlsx"); 
    }
    //guardar configuracion tabla
    public function prestamo_herramienta_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'prestamo_herramientas')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('prestamoherramienta');
    }
}
