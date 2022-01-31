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
use App\Serie;

class PrestamoHerramientaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }   

    public function prestamoherramienta(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('prestamo_herramientas', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('prestamo_herramienta_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('prestamo_herramienta_exportar_excel');
        return view('registros.prestamoherramienta.prestamoherramienta', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel'));
    }
    //obtener prestamos de herramienta
    public function prestamo_herramienta_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('prestamo_herramientas', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaPrestamoHerramienta::select($configuraciones_tabla['campos_consulta'])->where('periodo', $periodo);
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
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('total');
                    })
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->prestamo .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="terminarprestamo(\''.$data->prestamo .'\')">Terminar Prestamo</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->prestamo .'\')">Bajas</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('fecha', function($data){ return Carbon::parse($data->fecha)->toDateTimeString(); })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener series documento
    public function prestamo_herramienta_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'prestamo_herramientas')->where('Usuario', Auth::user()->user)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarseriedocumento(\''.$data->Serie.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener ultimo folio de la serie seleccionada
    public function prestamo_herramienta_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Prestamo_Herramienta',$request->serie);
        return response()->json($folio);
    }
    public function prestamo_herramienta_obtener_ultimo_id(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Prestamo_Herramienta',$request->serie);
        return response()->json($folio);
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
            $Asignacion_Herramienta = Asignacion_Herramienta::where('recibe_herramienta', $request->personalherramientacomun)->where('status', 'ALTA')->get();
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
    //obtener personal recibe por numero
    public function prestamo_herramienta_obtener_personal_recibe_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existepersonal = Personal::where('id', $request->numeropersonalrecibe)->where('id', '<>', $request->personalherramientacomun)->where('Status', 'ALTA')->count();
        if($existepersonal > 0){
            $personal = Personal::where('id', $request->numeropersonalrecibe)->where('id', '<>', $request->personalherramientacomun)->where('Status', 'ALTA')->first();
            $numero = $personal->id;
            $nombre = $personal->nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }
    //guardar prestamo herramienta
    public function prestamo_herramienta_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT prestamo_herramientas ON');
        $id = Helpers::ultimoidregistrotabla('App\Prestamo_Herramienta');
        $folio = Helpers::ultimofolioserieregistrotabla('App\Prestamo_Herramienta',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $prestamo = $folio.'-'.$request->serie;
		$Prestamo_Herramienta = new Prestamo_Herramienta;
		$Prestamo_Herramienta->id=$id;
        $Prestamo_Herramienta->folio=$folio;
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
        $Prestamo_Herramienta->periodo=$this->periodohoy;
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
        $BitacoraDocumento->Periodo = $this->periodohoy;
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
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $request->prestamodesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Prestamo_Herramienta::where('prestamo', $request->prestamodesactivar)
                                ->update([
                                    'motivo_baja' => $MotivoBaja,
                                    'status' => 'BAJA',
                                    'total' => '0.000000'
                                ]);
        //cambiar status de los detalles
        $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamodesactivar)->get();
        foreach($Prestamo_Herramienta_Detalle as $phd){
            //colocar en ceros cantidades detalles
            Prestamo_Herramienta_Detalle::where('id', $phd->id)
            ->update([
                'cantidad' => '0.000000',
                'total' => '0.000000',
                'status_prestamo' => 'BAJA'
            ]); 
        }
        return response()->json($Prestamo_Herramienta);
    }
    //terminar prestamo de herramienta
    public function prestamo_herramienta_terminar_prestamo(Request $request){
        //cambiar status del prestamo
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $request->prestamoterminarprestamo)->first();
        Prestamo_Herramienta::where('prestamo', $request->prestamoterminarprestamo)
        ->update([
            'status' => 'ENTREGADO',
        ]);
        //cambiar status de los detalles
        $Prestamo_Herramienta_Detalle = Prestamo_Herramienta_Detalle::where('prestamo', $request->prestamoterminarprestamo)->get();
        foreach($Prestamo_Herramienta_Detalle as $phd){
            Prestamo_Herramienta_Detalle::where('id', $phd->id)
            ->update([
                'status_prestamo' => 'ENTREGADO'
            ]); 
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
            $inicio_prestamo = Helpers::fecha_espanol_datetime($Prestamo_Herramienta->inicio_prestamo);
            $termino_prestamo = Helpers::fecha_espanol_datetime($Prestamo_Herramienta->termino_prestamo);
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
                            '<td class="tdmod"><input type="hidden" class="form-control iddetalleasignacionherramienta" name="iddetalleasignacionherramienta[]" value="'.$phd->id_detalle_asignacion_herramienta.'" readonly><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$phd->herramienta.'" readonly><b style="font-size:12px;">'.$phd->herramienta.'</b></td>'.
                            '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($phd->descripcion, ENT_QUOTES).'" readonly>'.htmlspecialchars($phd->descripcion, ENT_QUOTES).'</div></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'.$phd->unidad.'" readonly>'.$phd->unidad.'</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.$phd->cantidad.'" data-parsley-min="0.1" data-parsley-max="'.$phd->cantidad.'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" required readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.$phd->precio.'" onchange="formatocorrectoinputcantidades(this);" required readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpartida" name="totalpartida[]" value="'.$phd->precio.'" onchange="formatocorrectoinputcantidades(this);" required readonly></td>'.
                            '<td class="tdmod">'.
                              '<select name="estadopartida[]" class="form-control inputnextdet" style="width:100% !important;height: 28px !important;" required readonly disabled>'.
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
                $selectpersonal = $selectpersonal.'<option selected value='.$p->id.'>'.$p->nombre.' - '.$p->tipo_personal.'</option>';
            }else{
                $selectpersonal = $selectpersonal.'<option value='.$p->id.'>'.$p->nombre.' - '.$p->tipo_personal.'</option>';
            }
        }    
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($Prestamo_Herramienta->status == 'BAJA' || $Prestamo_Herramienta->status == 'ENTREGADO'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($Prestamo_Herramienta->status == 'BAJA' || $Prestamo_Herramienta->status == 'ENTREGADO'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($Prestamo_Herramienta->fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        } 
        $data = array(
            "Prestamo_Herramienta" => $Prestamo_Herramienta,
            "filasdetallesprestamo" => $filasdetallesprestamo,
            "Numero_Prestamo_Herramienta_Detalle" => $Numero_Prestamo_Herramienta_Detalle,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => $modificacionpermitida,
            "fecha" => Helpers::formatoinputdatetime($Prestamo_Herramienta->fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($Prestamo_Herramienta->fecha),
            "total" => Helpers::convertirvalorcorrecto($Prestamo_Herramienta->total),
            "personalrecibe" => $personalrecibe,
            "personalentrega" => $personalentrega,
            "selectpersonal" => $selectpersonal
        );
        return response()->json($data);
    }
    //guardar modificacion del prestamo
    public function prestamo_herramienta_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        //Modificar prestamo
        $prestamo = $request->folio.'-'.$request->serie;
        $Prestamo_Herramienta = Prestamo_Herramienta::where('prestamo', $prestamo)->first();
        Prestamo_Herramienta::where('prestamo', $prestamo)
        ->update([
            'termino_prestamo' => $request->terminoprestamo,
            'correo' => $request->correo,
            'observaciones' => $request->observaciones
        ]);
        if($Prestamo_Herramienta->termino_prestamo < $request->terminoprestamo){
            Prestamo_Herramienta::where('prestamo', $prestamo)
            ->update([
                'correo_enviado' => NULL
            ]);
        }
        /*
        if($Prestamo_Herramienta->termino_prestamo < $request->terminoprestamo){
            $Prestamo_Herramienta->correo_enviado = NULL;
        }
        $Prestamo_Herramienta->termino_prestamo = $request->terminoprestamo;
        $Prestamo_Herramienta->correo=$request->correo;
        $Prestamo_Herramienta->observaciones=$request->observaciones;
        $Prestamo_Herramienta->save();
        */
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "PRESTAMO DE HERRAMIENTA";
        $BitacoraDocumento->Movimiento = $prestamo;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Prestamo_Herramienta->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
    	return response()->json($Prestamo_Herramienta);
    }
    //exportar excel
    public function prestamo_herramienta_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('prestamo_herramientas', Auth::user()->id);
        return Excel::download(new PrestamoHerramientaExport($configuraciones_tabla['campos_consulta'],$request->periodo), "prestamosherramienta-".$request->periodo.".xlsx");   

    }
    //guardar configuracion tabla
    public function prestamo_herramienta_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('prestamo_herramientas', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'prestamo_herramientas')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='prestamo_herramientas';
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
        return redirect()->route('prestamoherramienta');
    }
}
