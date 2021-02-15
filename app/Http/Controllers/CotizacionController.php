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
use App\Exports\FormatosExcelCotizacionExport;
use App\Exports\CotizacionesExport;
use App\Cotizacion;
use App\CotizacionDetalle;
use App\TipoOrdenCompra;
use App\Serie;
use App\Proveedor;
use App\Almacen;
use App\BitacoraDocumento;
use App\Compra;
use App\CompraDetalle;
use App\Producto;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaCotizacion;
use App\Remision;
use App\RemisionDetalle;

class CotizacionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'cotizaciones_t')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function cotizaciones(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Cotizaciones');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('cotizaciones_guardar_configuracion_tabla');
        return view('registros.cotizaciones.cotizaciones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla'));
    }

    //obtener las asignaciones de herramienta
    public function cotizaciones_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            $data = VistaCotizacion::select($this->campos_consulta)->orderBy('id', 'DESC')->where('periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->cotizacion .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->cotizacion .'\')"><i class="material-icons">cancel</i></div> ';
                        $botonexcel =      '<a href="'.route('cotizaciones_crear_formato_excel',$data->cotizacion).'"><div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Crear formato excel"><i class="material-icons">format_indent_increase</i></div></a> ';
                        if($data->status == 'BAJA'){
                            $operaciones = '';
                        }else{
                            $operaciones =  $botoncambios.$botonbajas.$botonexcel;
                        }
                        return $operaciones;
                    })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    public function cotizaciones_obtener_ultimo_id(Request $request){
        $id = Helpers::ultimoidregistrotabla('App\Cotizacion');
        return response()->json($id);
    }

    public function cotizaciones_obtener_remisiones(Request $request){
        if($request->ajax()){
            $data = Remision::orderBy("Folio", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarremision('.$data->Folio.',\''.$data->Remision .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    public function cotizaciones_obtener_remision(Request $request){
        $remision = Remision::where('Remision', $request->Remision)->first();
        $cotizacionyaexistente = Cotizacion::where('num_remision', $request->Remision)->where('status','<>','BAJA')->count();
        //detalles remision
        $detallesremision = RemisionDetalle::where('Remision', $request->Remision)->get();
        $numerodetallesremision = RemisionDetalle::where('Remision', $request->Remision)->count();
        if($numerodetallesremision > 0){
            $filasdetallesremision = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallesremision as $dr){
                    $producto = Producto::where('Codigo', $dr->Codigo)->first();
                    $filasdetallesremision= $filasdetallesremision.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.');">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dr->Codigo.'" readonly>'.$dr->Codigo.'</td>'.
                        '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.$dr->Descripcion.'" readonly>'.$dr->Descripcion.'</div></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'.$dr->Unidad.'" readonly>'.$dr->Unidad.'</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" readonly required></td>'.
                        '<td class="tdmod">'.
                            '<select name="estadopartida[]" class="form-control divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'.
                                '<option selected disabled hidden>Selecciona</option>'.
                                '<option value="Nuevo">Nuevo</option>'.
                                '<option value="Usado">Usado</option>'.
                                '<option value="Reparado">Reparado</option>'.
                            '</select>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.1"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
            }
        }else{
            $filasdetallesremision = '';
        }        
        $data = array(
            "remision" => $remision,
            "filasdetallesremision" => $filasdetallesremision,
            "numerodetallesremision" => $numerodetallesremision,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdate($remision->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($remision->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($remision->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($remision->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($remision->Iva),
            "total" => Helpers::convertirvalorcorrecto($remision->Total),
            "cotizacionyaexistente" => $cotizacionyaexistente
        );
        return response()->json($data);
    }

    //guardar registro
    public function cotizaciones_guardar(Request $request){
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT cotizaciones_t ON');
        $id = Helpers::ultimoidregistrotabla('App\Cotizacion');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $cotizacion = $id.'-'.$request->serie;
        $Cotizacion = new Cotizacion;
        $Cotizacion->id=$id;
		$Cotizacion->cotizacion=$cotizacion;
		$Cotizacion->serie=$request->serie;
		$Cotizacion->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Cotizacion->num_remision=$request->remision;
        $Cotizacion->num_equipo=$request->equipo;
		$Cotizacion->subtotal=$request->subtotal;
		$Cotizacion->iva=$request->iva;
		$Cotizacion->total=$request->total;
        $Cotizacion->ot_tecnodiesel=$request->ottecnodiesel;
        $Cotizacion->ot_tyt=$request->ottyt;
        $Cotizacion->status="ALTA";
        $Cotizacion->usuario=Auth::user()->user;
        $Cotizacion->periodo=$request->periodohoy;
        $Cotizacion->save();
        DB::unprepared('SET IDENTITY_INSERT cotizaciones_t OFF');
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigopartida as $key => $codigopartida){  
            DB::unprepared('SET IDENTITY_INSERT cotizaciones_t_detalles ON');       
            $iddetalle = Helpers::ultimoidregistrotabla('App\CotizacionDetalle');       
            $CotizacionDetalle=new CotizacionDetalle;
            $CotizacionDetalle->id = $iddetalle;
            $CotizacionDetalle->id_cotizacion = $id;
            $CotizacionDetalle->cotizacion = $cotizacion;
            $CotizacionDetalle->fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $CotizacionDetalle->numero_parte = $codigopartida;
            $CotizacionDetalle->descripcion = $request->descripcionpartida [$key];
            $CotizacionDetalle->unidad = $request->unidadpartida [$key];
            $CotizacionDetalle->status_refaccion = $request->estadopartida [$key];
            $CotizacionDetalle->insumo = $request->insumopartida [$key];
            $CotizacionDetalle->precio =  $request->preciopartida [$key];
            $CotizacionDetalle->cantidad =  $request->cantidadpartida  [$key];
            $CotizacionDetalle->importe = $request->importepartida [$key];
            $CotizacionDetalle->item = $item;
            $CotizacionDetalle->save();
            $item++;
            DB::unprepared('SET IDENTITY_INSERT cotizaciones_t_detalles OFF');
        }
    	return response()->json($Cotizacion); 
    }

    //bajas
    public function cotizaciones_alta_o_baja(Request $request){
        $Cotizacion = Cotizacion::where('cotizacion', $request->cotizaciondesactivar)->first();
        $Cotizacion->motivo_baja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $Cotizacion->status = 'BAJA';
        $Cotizacion->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES";
        $BitacoraDocumento->Movimiento = $request->cotizaciondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Cotizacion->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Cotizacion);
    }

    //obtener registro
    public function cotizaciones_obtener_cotizacion(Request $request){
        $cotizacion = Cotizacion::where('cotizacion', $request->cotizacionmodificar)->first();
        $remision = Remision::where('Remision', $cotizacion->num_remision)->first();
        //detalles
        $detallescotizacion= CotizacionDetalle::where('cotizacion', $request->cotizacionmodificar)->get();
        $numerodetallescotizacion = CotizacionDetalle::where('cotizacion', $request->cotizacionmodificar)->count();
        if($numerodetallescotizacion > 0){
            $filasdetallescotizacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallescotizacion as $dc){
                if($dc->status_refaccion == 'Nuevo'){
                    $opciones = '<option value="Nuevo" selected>Nuevo</option>'.
                                '<option value="Usado">Usado</option>'.
                                '<option value="Reparado">Reparado</option>';
                }else if($dc->status_refaccion == 'Usado'){
                    $opciones = '<option value="Nuevo">Nuevo</option>'.
                                '<option value="Usado" selected>Usado</option>'.
                                '<option value="Reparado">Reparado</option>';
                }else if($dc->status_refaccion == 'Reparado'){
                    $opciones = '<option value="Nuevo">Nuevo</option>'.
                                '<option value="Usado">Usado</option>'.
                                '<option value="Reparado" selected>Reparado</option>';
                }
                $producto = Producto::where('Codigo', $dc->numero_parte)->first();
                $filasdetallescotizacion= $filasdetallescotizacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->item.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dc->numero_parte.'" readonly>'.$dc->numero_parte.'</td>'.
                    '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.$dc->descripcion.'" readonly>'.$dc->descripcion.'</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'.$dc->unidad.'" readonly>'.$dc->unidad.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$dc->insumo.'" readonly required></td>'.
                    '<td class="tdmod">'.
                        '<select name="estadopartida[]" class="form-control divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'.
                            $opciones.
                        '</select>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->cantidad).'" data-parsley-min="0.1"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }        
        $data = array(
            "cotizacion" => $cotizacion,
            "remision" => $remision,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdate($cotizacion->fecha),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->subtotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->total),
            "modificacionpermitida" => 1
        );
        return response()->json($data);        
    }

    //modificar
    public function cotizaciones_guardar_modificacion(Request $request){
        $cotizacion = $request->folio.'-'.$request->serie;
        $Cotizacion = Cotizacion::where('cotizacion', $cotizacion)->first();
		$Cotizacion->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$Cotizacion->num_remision=$request->remision;
        $Cotizacion->num_equipo=$request->equipo;
		$Cotizacion->subtotal=$request->subtotal;
		$Cotizacion->iva=$request->iva;
		$Cotizacion->total=$request->total;
        $Cotizacion->ot_tecnodiesel=$request->ottecnodiesel;
        $Cotizacion->ot_tyt=$request->ottyt;
        $Cotizacion->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Cotizacion->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        foreach ($request->codigopartida as $key => $codigopartida){  
            CotizacionDetalle::where('cotizacion', $cotizacion)
            ->where('item', $request->itempartida [$key])
            ->update([
                'fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                'status_refaccion' => $request->estadopartida [$key],
                'precio' => $request->preciopartida [$key],
                'cantidad' => $request->cantidadpartida  [$key],
                'importe' => $request->importepartida [$key]
            ]);
        }
    	return response()->json($Cotizacion);
    }

    //exportar excel
    public function cotizaciones_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new CotizacionesExport($this->campos_consulta), "cotizaciones.xlsx");   
    }

    //guardar configuracion tabla
    public function cotizaciones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'cotizaciones_t')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('cotizaciones');
    }

    public function cotizaciones_crear_formato_excel($cotizacion){
        $contardetallescotizacion = CotizacionDetalle::where('cotizacion', $cotizacion)->count();
        $numeroinicialcelda = 9;
        $numerofinalcelda = $numeroinicialcelda + $contardetallescotizacion - 1;
        $celdasmerge = 'B'.$numeroinicialcelda.':B'.$numerofinalcelda;
        return Excel::download(new FormatosExcelCotizacionExport($cotizacion, $numeroinicialcelda, $numerofinalcelda, $this->numerodecimales, $this->empresa), "formatocotizacion".$cotizacion.".xlsx"); 
    }
}
