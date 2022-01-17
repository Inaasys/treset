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
    }

    public function cotizaciones(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('cotizaciones_t', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('cotizaciones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cotizaciones_exportar_excel');
        return view('registros.cotizaciones.cotizaciones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel'));
    }

    //obtener las asignaciones de herramienta
    public function cotizaciones_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('cotizaciones_t', Auth::user()->id);
            $periodo = $request->periodo;
            $data = VistaCotizacion::select($configuraciones_tabla['campos_consulta'])->where('periodo', $periodo);
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
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->cotizacion .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->cotizacion .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('cotizaciones_crear_formato_excel',$data->cotizacion).'" target="_blank">Crear Formato Excel</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('fecha', function($data){ return Carbon::parse($data->fecha)->toDateString(); })
                    ->addColumn('subtotal', function($data){ return $data->subtotal; })
                    ->addColumn('iva', function($data){ return $data->iva; })
                    ->addColumn('total', function($data){ return $data->total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function cotizaciones_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'cotizaciones_t')->where('Usuario', Auth::user()->user)->get();
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
    public function cotizaciones_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Cotizacion',$request->serie);
        return response()->json($folio);
    }
    public function cotizaciones_obtener_ultimo_id(Request $request){
        $folio = Helpers::ultimofolioserieregistrotabla('App\Cotizacion',$request->serie);
        return response()->json($folio);
    }

    public function cotizaciones_obtener_remisiones(Request $request){
        if($request->ajax()){
            $arrayremisionesseleccionadas = Array();
            foreach(explode(",", $request->stringremisionesseleccionadas) as $remision){
                array_push($arrayremisionesseleccionadas, $remision);
            }
            $data = Remision::where('Status', 'POR FACTURAR')->orderBy("Folio", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarremision('.$data->Folio.',\''.$data->Remision .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Selecciona', function($data) use ($arrayremisionesseleccionadas){
                        if(in_array($data->Remision, $arrayremisionesseleccionadas) == true){
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required checked>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }else{
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }
                        return $checkbox;
                    })
                    ->rawColumns(['operaciones','Selecciona'])
                    ->make(true);
        }
    }

    public function cotizaciones_obtener_remision(Request $request){
        //detalles remision
        $filasremisiones = '';
        $contadorfilas = $request->contadorfilas;
        $partida = $request->partida;
        $tipooperacion = $request->tipooperacion;
        foreach(explode(",", $request->stringremisionesseleccionadas) as $r){


            $remision = Remision::where('Remision', $r)->first();
            //detalles remision
            $detallesremision = RemisionDetalle::where('Remision', $r)->get();
            $numerodetallesremision = RemisionDetalle::where('Remision', $r)->count();
            if($numerodetallesremision > 0){
                foreach($detallesremision as $dr){
                        $producto = Producto::where('Codigo', $dr->Codigo)->first();
                        $filasremisiones= $filasremisiones.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorfilas.');">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 50]">'.$dr->Codigo.'</td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dr->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 50]">'.$dr->Unidad.'</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" readonly required></td>'.
                            '<td class="tdmod">'.
                                '<select name="estadopartida[]" class="form-control inputnextdet divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'.
                                    '<option value="Nuevo">Nuevo</option>'.
                                    '<option value="Usado">Usado</option>'.
                                    '<option value="Reparado">Reparado</option>'.
                                '</select>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.1"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm numeroremisionpartida" name="numeroremisionpartida[]" value="'.$remision->Remision.'" readonly required></td>'.
                            '<td class="tdmod"><input type="datetime-local" class="form-control divorinputmodxl fecharemisionpartida" name="fecharemisionpartida[]" value="'.Helpers::formatoinputdatetime($remision->Fecha).'" readonly required></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm numeroequipopartida" name="numeroequipopartida[]" value="'.$remision->Eq.'" readonly required></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ottecnodieselpartida" name="ottecnodieselpartida[]" value="'.$remision->Referencia.'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ottytpartida" name="ottytpartida[]" value="'.$remision->Os.'" readonly required></td>'.
                        '</tr>';
                        $partida++;
                        $contadorfilas++;
                }
            }


        }
        $data = array(
            "filasremisiones" => $filasremisiones,
            "contadorfilas" => $contadorfilas,
            "partida" => $partida,
        );
        return response()->json($data); 

    }

    //guardar registro
    public function cotizaciones_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        DB::unprepared('SET IDENTITY_INSERT cotizaciones_t ON');
        $id = Helpers::ultimoidregistrotabla('App\Cotizacion');
        $folio = Helpers::ultimofolioserieregistrotabla('App\Cotizacion',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $cotizacion = $folio.'-'.$request->serie;
        $Cotizacion = new Cotizacion;
        $Cotizacion->id=$id;
        $Cotizacion->folio=$folio;
		$Cotizacion->cotizacion=$cotizacion;
		$Cotizacion->serie=$request->serie;
		$Cotizacion->fecha=Carbon::parse($request->fecha)->toDateTimeString();
		//$Cotizacion->num_remision=$request->remision;
        //$Cotizacion->num_equipo=$request->equipo;
		$Cotizacion->subtotal=$request->subtotal;
		$Cotizacion->iva=$request->iva;
		$Cotizacion->total=$request->total;
        //$Cotizacion->ot_tecnodiesel=$request->ottecnodiesel;
        //$Cotizacion->ot_tyt=$request->ottyt;
        $Cotizacion->status="ALTA";
        $Cotizacion->usuario=Auth::user()->user;
        $Cotizacion->periodo=$this->periodohoy;
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
        $BitacoraDocumento->Periodo = $this->periodohoy;
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

    //veririfcar baja
    public function cotizaciones_verificar_uso_en_modulos(Request $request){
        $Cotizacion = Cotizacion::where('cotizacion', $request->cotizaciondesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($Cotizacion->fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'status' => $Cotizacion->status
        );
        return response()->json($data);
    }

    //bajas
    public function cotizaciones_alta_o_baja(Request $request){
        $Cotizacion = Cotizacion::where('cotizacion', $request->cotizaciondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Cotizacion::where('cotizacion', $request->cotizaciondesactivar)
                ->update([
                    'motivo_baja' => $MotivoBaja,
                    'status' => 'BAJA',
                    //'num_remision' => '',
                    //'num_equipo' => '',
                    'subtotal' => '0.000000',
                    'iva' => '0.000000',
                    'total' => '0.000000'
                ]);
        $detalles = CotizacionDetalle::where('cotizacion', $request->cotizaciondesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            CotizacionDetalle::where('cotizacion', $request->cotizaciondesactivar)
                            ->where('Item', $detalle->item)
                            ->update([
                                'cantidad' => '0.000000',
                                'importe' => '0.000000'
                            ]);
        }        
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
        //$remision = Remision::where('Remision', $cotizacion->num_remision)->first();
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
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$dc->numero_parte.'" readonly data-parsley-length="[1, 50]">'.$dc->numero_parte.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadpartida" name="unidadpartida[]" value="'.$dc->unidad.'" readonly data-parsley-length="[1, 50]">'.$dc->unidad.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$dc->insumo.'" readonly required></td>'.
                    '<td class="tdmod">'.
                        '<select name="estadopartida[]" class="form-control inputnextdet divorinputmodmd" style="width:100% !important;height: 28px !important;" required>'.
                            $opciones.
                        '</select>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->cantidad).'" data-parsley-min="0.1"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }   
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($cotizacion->status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($cotizacion->status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($cotizacion->fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }      
        $data = array(
            "cotizacion" => $cotizacion,
            "remision" => $remision,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($cotizacion->fecha),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->subtotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->total),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);        
    }

    //modificar
    public function cotizaciones_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $cotizacion = $request->folio.'-'.$request->serie;
        $Cotizacion = Cotizacion::where('cotizacion', $cotizacion)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles
        //array partidas antes de modificacion
        $ArrayDetallesCotizacionAnterior = Array();
        $DetallesCotizacionAnterior = CotizacionDetalle::where('cotizacion', $cotizacion)->get();
        foreach($DetallesCotizacionAnterior as $detalle){
            array_push($ArrayDetallesCotizacionAnterior, $detalle->numero_parte);
        }
        //array partida despues de modificacion
        $ArrayDetallesCotizacionNuevo = Array();
        foreach ($request->codigopartida as $key => $nuevocodigo){ 
            array_push($ArrayDetallesCotizacionNuevo, $nuevocodigo);
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesCotizacionAnterior, $ArrayDetallesCotizacionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                //eliminar detalle de la remision eliminado
                $eliminardetallecotizacion = CotizacionDetalle::where('cotizacion', $cotizacion)->where('numero_parte', $eliminapartida)->forceDelete();
            }
        }
        //modificar remision
        Cotizacion::where('cotizacion', $cotizacion)
        ->update([
            'fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            //'num_remision'=>$request->remision,
            //'num_equipo'=>$request->equipo,
            'subtotal'=>$request->subtotal,
            'iva'=>$request->iva,
            'total'=>$request->total,
            //'ot_tecnodiesel'=>$request->ottecnodiesel,
            //'ot_tyt'=>$request->ottyt
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Cotizacion->status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigopartida as $key => $codigopartida){   
            //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
            if($request->agregadoen [$key] == 'modificacion'){
                $contaritems = CotizacionDetalle::select('item')->where('cotizacion', $cotizacion)->orderBy('item', 'DESC')->take(1)->count();
                if($contaritems > 0){
                    $item = CotizacionDetalle::select('item')->where('cotizacion', $cotizacion)->orderBy('item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;  
                }else{
                    $ultimoitem = 1;
                }              
                DB::unprepared('SET IDENTITY_INSERT cotizaciones_t_detalles ON');       
                $iddetalle = Helpers::ultimoidregistrotabla('App\CotizacionDetalle');       
                $CotizacionDetalle=new CotizacionDetalle;
                $CotizacionDetalle->id = $iddetalle;
                $CotizacionDetalle->id_cotizacion = $Cotizacion->id;
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
                $CotizacionDetalle->item = $ultimoitem;
                $CotizacionDetalle->save();
                DB::unprepared('SET IDENTITY_INSERT cotizaciones_t_detalles OFF');
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                CotizacionDetalle::where('cotizacion', $cotizacion)
                ->where('item', $request->itempartida [$key])
                ->update([
                    'fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'descripcion' => $request->descripcionpartida [$key],
                    'status_refaccion' => $request->estadopartida [$key],
                    'precio' => $request->preciopartida [$key],
                    'cantidad' => $request->cantidadpartida  [$key],
                    'importe' => $request->importepartida [$key]
                ]);
            }
        }
    	return response()->json($Cotizacion);
    }

    //exportar excel
    public function cotizaciones_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('cotizaciones_t', Auth::user()->id);
        return Excel::download(new CotizacionesExport($configuraciones_tabla['campos_consulta'],$request->periodo), "cotizaciones-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function cotizaciones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('cotizaciones_t', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'cotizaciones_t')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='cotizaciones_t';
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
