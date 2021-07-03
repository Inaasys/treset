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
use App\Exports\AjustesInventarioExport;
use App\AjusteInventario;
use App\AjusteInventarioDetalle;
use App\Serie;
use App\Almacen;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Configuracion_Tabla;
use App\VistaAjusteInventario;
use App\VistaObtenerExistenciaProducto;
use Config;
use Mail;

class AjusteInventarioController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'AjustesInventario')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function ajustesinventario(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('ajustesinventario_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('ajustesinventario_exportar_excel');
        $rutacreardocumento = route('ajustesinventario_generar_pdfs');
        return view('registros.ajustesinventario.ajustesinventario', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    
    //obtener las asignaciones de herramienta
    public function ajustesinventario_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            //$data = VistaAjusteInventario::select($this->campos_consulta)->where('periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaAjusteInventario::select($this->campos_consulta)->where('periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query){
                        $query->orderBy('Fecha', 'DESC');
                        $query->orderBy('Serie', 'ASC');
                        $query->orderBy('Folio', 'DESC');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Ajuste .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Ajuste .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('ajustesinventario_generar_pdfs_indiv',$data->Ajuste).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Ajuste .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener series documento
    public function ajustesinventario_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'AjustesInventario')->where('Usuario', Auth::user()->user)->get();
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
    public function ajustesinventario_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimi registro
    public function ajustesinventario_obtener_ultimo_id(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->serie);
        return response()->json($folio);
    }

    //obtener almacenes
    public function ajustesinventario_obtener_almacenes(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener almacen por numero
    public function ajustesinventario_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
        if($existealmacen > 0){
            $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
            $numero = $almacen->Numero;
            $nombre = $almacen->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }

    //obtener productos
    public function ajustesinventario_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $numeroalmacen = $request->numeroalmacen;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        if($data->Almacen == $numeroalmacen || $data->Almacen == NULL){
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                        }else{
                            $boton = '';
                        }    
                        return $boton;
                    })
                    ->addColumn('Existencias', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Existencias);
                    })
                    ->addColumn('Costo', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->Costo);
                    })
                    ->addColumn('SubTotal', function($data){ 
                        return Helpers::convertirvalorcorrecto($data->SubTotal);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener existencias
    public function ajustesinventario_obtener_existencias_partida(Request $request){
        $ContarExistencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
            $existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $existencias = Helpers::convertirvalorcorrecto(0);
        }
        $ajuste = $request->folio.'-'.$request->serie;
        $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->count();
        $nuevaexistencia = 0;
        if($detalleajusteinventario > 0){
            $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->first();
            $nuevaexistencia = $existencias + $detalleajusteinventario->Cantidad;
        }else{
            $nuevaexistencia = $existencias;
        }
        return response()->json(Helpers::convertirvalorcorrecto($nuevaexistencia));
    }

    //guardar
    public function ajustesinventario_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\AjusteInventario',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $ajuste = $folio.'-'.$request->serie;
        $AjusteInventario = new AjusteInventario;
        $AjusteInventario->Ajuste=$ajuste;
        $AjusteInventario->Serie=$request->serie;
        $AjusteInventario->Folio=$folio;
        $AjusteInventario->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $AjusteInventario->Obs=$request->observaciones;
        $AjusteInventario->Almacen=$request->numeroalmacen;
        $AjusteInventario->Total=$request->total;
        $AjusteInventario->Status="ALTA";
        $AjusteInventario->Usuario=Auth::user()->user;
        $AjusteInventario->Periodo=$this->periodohoy;
        $AjusteInventario->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $AjusteInventarioDetalle=new AjusteInventarioDetalle;
            $AjusteInventarioDetalle->Ajuste = $ajuste;
            $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
            $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
            $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
            $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
            $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
            $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
            $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
            $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
            $AjusteInventarioDetalle->Item = $item;
            $AjusteInventarioDetalle->save();
            $item++;
            //modificar las existencias del código en la tabla de existencias
            $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
            if($ContarExistencia > 0){
                $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                Existencia::where('Codigo', $codigoproductopartida)
                ->where('Almacen', $request->numeroalmacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                ]);
            }else{
                $Existencia = new Existencia;
                $Existencia->Codigo = $codigoproductopartida;
                $Existencia->Almacen = $request->numeroalmacen;
                $Existencia->Existencias = $request->existencianuevapartida [$key];
                $Existencia->save();
            }
        }
        return response()->json($AjusteInventario);
    }

    //verificar si se puede dar de baja
    public function ajustesinventario_verificar_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        $errores = '';
        foreach($detalles as $detalle){
            if($detalle->Entradas > 0){
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                if($detalle->Entradas > $Existencia->Existencias){
                    $errores = $errores.'Error el ajuste no se puede cancelar, no hay existencias suficientes en el almacen:'.$AjusteInventario->Almacen.' para el código:'.$detalle->Codigo.'<br>';
                }

            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($AjusteInventario->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $AjusteInventario->Status
        );
        return response()->json($data);
    }

    //bajas
    public function ajustesinventario_alta_o_baja(Request $request){
        $AjusteInventario = AjusteInventario::where('Ajuste', $request->ajustedesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        AjusteInventario::where('Ajuste', $request->ajustedesactivar)
        ->update([
            'MotivoBaja' => $MotivoBaja,
            'Status' => 'BAJA',
            'Total' => '0.000000'
        ]);
        $detalles = AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)->get();
        foreach($detalles as $detalle){
            //entradas
            if($detalle->Entradas > 0){
                //restar las entradas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $NuevaExistenciaEntradas = $Existencia->Existencias-$detalle->Entradas;
                Existencia::where('Codigo', $detalle->Codigo)
                ->where('Almacen', $AjusteInventario->Almacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                ]);
            }
            //salidas
            if($detalle->Salidas > 0){
                //sumar las salidas
                $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $AjusteInventario->Almacen)->first();
                $NuevaExistenciaSalidas = $Existencia->Existencias+$detalle->Salidas;
                Existencia::where('Codigo', $detalle->Codigo)
                ->where('Almacen', $AjusteInventario->Almacen)
                ->update([
                    'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                ]);
            }
            //colocar en ceros cantidades
            AjusteInventarioDetalle::where('Ajuste', $request->ajustedesactivar)
                                    ->where('Item', $detalle->Item)
                                    ->update([
                                        'Existencias' => '0.000000',
                                        'Entradas' => '0.000000',
                                        'Salidas' => '0.000000',
                                        'Real' => '0.000000'
                                    ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $request->ajustedesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($AjusteInventario);
    }

    //obtener nuevos datos fila
    public function ajustesinventario_obtener_nuevos_datos_fila(Request $request){
        $ContarExistencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->numeroalmacen)->first();
            $existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $existencias = Helpers::convertirvalorcorrecto(0);
        }
        $ajuste = $request->folio.'-'.$request->serie;
        $ajusteinventario = AjusteInventario::where('Ajuste', $ajuste)->first();
        $contardetalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->count();
        $nuevaexistencia = 0;
        $existencianuevapartida = 0;
        $entradaspartida = $request->entradaspartida;
        $salidaspartida = $request->salidaspartida;
        $entradaspartidadb = 0;
        $salidaspartidadb = 0;
        if($contardetalleajusteinventario > 0){
            $detalleajusteinventario = AjusteInventarioDetalle::where('Ajuste', $ajuste)->where('Codigo', $request->codigopartida)->first();
            if($request->numeroalmacen == $ajusteinventario->Almacen){
                $nuevaexistencia = $detalleajusteinventario->Existencias;
            }else{
                $nuevaexistencia = $existencias;
            }
            $existenciaactualpartida = $nuevaexistencia;
            $entradaspartidadb = $detalleajusteinventario->Entradas;
            $salidaspartidadb = $detalleajusteinventario->Salidas;
        }else{
            $nuevaexistencia = $existencias;
            $existenciaactualpartida = $nuevaexistencia;
        }
        $existencianuevapartida = $existenciaactualpartida+$entradaspartida-$salidaspartida;
        $nuevosdatosfila = array(
            'nuevaexistencia' => Helpers::convertirvalorcorrecto($nuevaexistencia),
            'existencianuevapartida' => Helpers::convertirvalorcorrecto($existencianuevapartida)
        );
        return response()->json($nuevosdatosfila);
    }

    //obtener ajuste
    public function ajustesinventario_obtener_ajuste(Request $request){
        $ajuste = AjusteInventario::where('Ajuste', $request->ajustemodificar)->first();
        $almacen = Almacen::where('Numero', $ajuste->Almacen)->first();
        //detalles
        $detallesajuste= AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->get();
        $numerodetallesajuste = AjusteInventarioDetalle::where('Ajuste', $request->ajustemodificar)->count();
        if($numerodetallesajuste > 0){
            $filasdetallesajuste = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallesajuste as $da){
                $producto = Producto::where('Codigo', $da->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $ajuste->Almacen)->first();
                $subtotalentradaspartida = $da->Entradas*$da->Costo;
                $subtotalsalidaspartida = $da->Salidas*$da->Costo;
                $parsleymax = $Existencia->Existencias+$da->Salidas;
                $filasdetallesajuste= $filasdetallesajuste.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$da->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd codigoproductopartida" name="codigoproductopartida[]" value="'.$da->Codigo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" value="'.$da->Descripcion.'" readonly data-parsley-length="[1, 255]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm unidadproductopartida" name="unidadproductopartida[]" value="'.$da->Unidad.'" readonly data-parsley-length="[1, 5]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'.Helpers::convertirvalorcorrecto($da->Existencias).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartidadb" name="entradaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm entradaspartida" name="entradaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Entradas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularsubtotalentradas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');colocardataparsleyminentradas('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartidadb" name="salidaspartidadb[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm salidaspartida" name="salidaspartida[]" value="'.Helpers::convertirvalorcorrecto($da->Salidas).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" data-parsley-existencias="'.$parsleymax.'" onchange="formatocorrectoinputcantidades(this);calcularsubtotalsalidas('.$contadorfilas.');calcularexistencianueva('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.');colocardataparsleyminsalidas('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existencianuevapartida" name="existencianuevapartida[]" value="'.Helpers::convertirvalorcorrecto($da->Real).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($da->Costo).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);cambiocosto('.$contadorfilas.');"></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalentradaspartida" name="subtotalentradaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalentradaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalsalidaspartida" name="subtotalsalidaspartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalsalidaspartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesajuste = '';
        }        
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ajuste->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ajuste->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($ajuste->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "ajuste" => $ajuste,
            "almacen" => $almacen,
            "filasdetallesajuste" => $filasdetallesajuste,
            "numerodetallesajuste" => $numerodetallesajuste,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($ajuste->Fecha),
            "total" => Helpers::convertirvalorcorrecto($ajuste->Total),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data); 
    }

    //modificar
    public function ajustesinventario_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $ajuste = $request->folio.'-'.$request->serie;
        $AjusteInventario = AjusteInventario::where('Ajuste', $ajuste)->first();
        $numeroalmacendb = $request->numeroalmacendb;
        $numeroalmacen = $request->numeroalmacen;
        if($numeroalmacendb == $numeroalmacen){
            //array detalles antes de modificacion
            $ArrayDetallesAjusteAnterior = Array();
            $DetallesAjusteAnterior = AjusteInventarioDetalle::where('Ajuste', $ajuste)->get();
            foreach($DetallesAjusteAnterior as $detalle){
                array_push($ArrayDetallesAjusteAnterior, $detalle->Ajuste.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array detalles despues de modificacion
            $ArrayDetallesAjusteNuevo = Array();
            foreach ($request->codigoproductopartida as $key => $nuevocodigo){
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesAjusteNuevo, $ajuste.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                } 
            }  
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesAjusteAnterior, $ArrayDetallesAjusteNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detalleajuste = AjusteInventarioDetalle::where('Ajuste', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    //entradas
                    if($detalleajuste->Entradas > 0){
                        //restar las entradas
                        $Existencia = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                        $NuevaExistenciaEntradas = $Existencia->Existencias-$detalleajuste->Entradas;
                        Existencia::where('Codigo', $explode_d[1])
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                        ]);
                    }
                    //salidas
                    if($detalleajuste->Salidas > 0){
                        //sumar las salidas
                        $Existencia = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                        $NuevaExistenciaSalidas = $Existencia->Existencias+$detalleajuste->Salidas;
                        Existencia::where('Codigo', $explode_d[1])
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                        ]);
                    }
                    //eliminar detalle
                    $eliminardetalle= AjusteInventarioDetalle::where('Ajuste', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
        }else{
            $detallesajustes = AjusteInventarioDetalle::where('Ajuste', $ajuste)->get();
            foreach($detallesajustes as $da){
                //entradas
                if($da->Entradas > 0){
                    //restar las entradas
                    $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $request->numeroalmacendb)->first();
                    $NuevaExistenciaEntradas = $Existencia->Existencias-$da->Entradas;
                    Existencia::where('Codigo', $da->Codigo)
                    ->where('Almacen', $request->numeroalmacendb)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaEntradas)
                    ]);
                }
                //salidas
                if($da->Salidas > 0){
                    //sumar las salidas
                    $Existencia = Existencia::where('Codigo', $da->Codigo)->where('Almacen', $request->numeroalmacendb)->first();
                    $NuevaExistenciaSalidas = $Existencia->Existencias+$da->Salidas;
                    Existencia::where('Codigo', $da->Codigo)
                    ->where('Almacen', $request->numeroalmacendb)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($NuevaExistenciaSalidas)
                    ]);
                }
                //eliminar detalle
                $eliminardetalle= AjusteInventarioDetalle::where('Ajuste', $da->Ajuste)->where('Codigo', $da->Codigo)->where('Item', $da->Item)->forceDelete();
            }
        }
        //modificar ajuste
        AjusteInventario::where('Ajuste', $ajuste)
        ->update([
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Obs'=>$request->observaciones,
            'Almacen'=>$request->numeroalmacen,
            'Total'=>$request->total
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "AJUSTES DE INVENTARIO";
        $BitacoraDocumento->Movimiento = $ajuste;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $AjusteInventario->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        //if al ajuste no se le modifico el almacen
        if($numeroalmacendb == $numeroalmacen){  
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){ 
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->agregadoen [$key] == 'modificacion'){
                    $contardetalles = AjusteInventarioDetalle::where('Ajuste', $ajuste)->count();
                    if($contardetalles > 0){
                        $item = AjusteInventarioDetalle::select('Item')->where('Ajuste', $ajuste)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $AjusteInventarioDetalle=new AjusteInventarioDetalle;
                    $AjusteInventarioDetalle->Ajuste = $ajuste;
                    $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
                    $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
                    $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
                    $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
                    $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
                    $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
                    $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
                    $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
                    $AjusteInventarioDetalle->Item = $ultimoitem;
                    $AjusteInventarioDetalle->save();
                    //modificar las existencias del código en la tabla de existencias
                    $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                    if($ContarExistencia > 0){
                        $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                        Existencia::where('Codigo', $codigoproductopartida)
                        ->where('Almacen', $request->numeroalmacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                        ]);
                    }else{
                        $Existencia = new Existencia;
                        $Existencia->Codigo = $codigoproductopartida;
                        $Existencia->Almacen = $request->numeroalmacen;
                        $Existencia->Existencias = $request->existencianuevapartida [$key];
                        $Existencia->save();
                    }
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    //modificar las existencias del código en la tabla de existencias
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                                ]);
                    //existencias actuales
                    $existenciaactualpartida = $request->existenciaactualpartida [$key] + $request->salidaspartidadb [$key] - $request->entradaspartidadb [$key];
                    AjusteInventarioDetalle::where('Ajuste', $ajuste)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Existencias' => $request->existenciaactualpartida [$key],
                        'Entradas' => $request->entradaspartida [$key],
                        'Salidas' => $request->salidaspartida  [$key],
                        'Real' => $request->existencianuevapartida [$key],
                        'Costo' => $request->costopartida [$key]
                    ]);
                }  
            }
        }else{
            $item = 1;
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){ 
                //si no ingresa los detalles con el nuevo almacen
                $AjusteInventarioDetalle=new AjusteInventarioDetalle;
                $AjusteInventarioDetalle->Ajuste = $ajuste;
                $AjusteInventarioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $AjusteInventarioDetalle->Codigo = $codigoproductopartida;
                $AjusteInventarioDetalle->Descripcion = $request->nombreproductopartida [$key];
                $AjusteInventarioDetalle->Unidad = $request->unidadproductopartida [$key];
                $AjusteInventarioDetalle->Existencias =  $request->existenciaactualpartida   [$key];
                $AjusteInventarioDetalle->Entradas =  $request->entradaspartida [$key];
                $AjusteInventarioDetalle->Salidas = $request->salidaspartida [$key];
                $AjusteInventarioDetalle->Real = $request->existencianuevapartida  [$key];
                $AjusteInventarioDetalle->Costo = $request->costopartida  [$key];
                $AjusteInventarioDetalle->Item = $item;
                $AjusteInventarioDetalle->save();
                $item++;
                //modificar las existencias del código en la tabla de existencias
                $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistencia > 0){
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    Existencia::where('Codigo', $codigoproductopartida)
                    ->where('Almacen', $request->numeroalmacen)
                    ->update([
                        'Existencias' => Helpers::convertirvalorcorrecto($request->existencianuevapartida [$key])
                    ]);
                }else{
                    $Existencia = new Existencia;
                    $Existencia->Codigo = $codigoproductopartida;
                    $Existencia->Almacen = $request->numeroalmacen;
                    $Existencia->Existencias = $request->existencianuevapartida [$key];
                    $Existencia->save();
                }
            }
        } 
        return response()->json($AjusteInventario);
    }

    //buscar folio
    public function ajustesinventario_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = AjusteInventario::where('Ajuste', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Ajuste .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }

    //generar documento pdf
    public function ajustesinventario_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $ajustes = AjusteInventario::whereIn('Ajuste', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $ajustes = AjusteInventario::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ajustes as $a){
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            $data[]=array(
                      "ajuste"=>$a,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //generacion de formato en PDF
    public function ajustesinventario_generar_pdfs_indiv($documento){
        $ajustes = AjusteInventario::where('Ajuste', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ajustes as $a){
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            $data[]=array(
                      "ajuste"=>$a,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function ajustesinventario_obtener_datos_envio_email(Request $request){
        $ajusteinventario = AjusteInventario::where('Ajuste', $request->documento)->first();
        $data = array(
            'ajusteinventario' => $ajusteinventario,
            'emailde' => Config::get('mail.from.address'),
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function ajustesinventario_enviar_pdfs_email(Request $request){
        $ajustes = AjusteInventario::where('Ajuste', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ajustes as $a){
            $ajustedetalle = AjusteInventarioDetalle::where('Ajuste', $a->Ajuste)->get();
            $datadetalle=array();
            foreach($ajustedetalle as $ad){
                $producto = Producto::where('Codigo', $ad->Codigo)->first();
                $totalentradas=$ad->Entradas*$ad->Costo;
                $totalsalidas=$ad->Salidas*$ad->Costo;
                if($totalentradas >= $totalsalidas){
                    $total = $totalentradas - $totalsalidas;
                }else{
                    $total = $totalsalidas - $totalentradas;
                }
                $datadetalle[]=array(
                    "codigodetalle"=>$ad->Codigo,
                    "descripciondetalle"=>$ad->Descripcion,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "existendetalle"=> Helpers::convertirvalorcorrecto($ad->Existencias),
                    "entradasdetalle" => Helpers::convertirvalorcorrecto($ad->Entradas),
                    "salidasdetalle" => Helpers::convertirvalorcorrecto($ad->Salidas),
                    "realdetalle" => Helpers::convertirvalorcorrecto($ad->Real),
                    "costodetalle" => Helpers::convertirvalorcorrecto($ad->Costo),
                    "totaldetalle" => Helpers::convertirvalorcorrecto($total)
                );
            } 
            $data[]=array(
                      "ajuste"=>$a,
                      "totalajuste"=>Helpers::convertirvalorcorrecto($a->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ajustesinventario.formato_pdf_ajustesinventario', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($correos)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "AjusteInventarioNo".$emaildocumento.".pdf");
            });
        } catch(\Exception $e) {
            $receptor = 'osbaldo.anzaldo@utpcamiones.com.mx';
            $correos = ['osbaldo.anzaldo@utpcamiones.com.mx'];
            $msj = 'Error al enviar correo';
            Mail::send('correos.errorenvio.error', compact('e','msj'), function($message) use ($receptor, $correos) {
                $message->to($receptor)
                        ->cc($correos)
                        ->subject('Error al enviar correo nuevo usuario');
            });
        }
    }

    public function ajustesinventario_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new AjustesInventarioExport($this->campos_consulta,$request->periodo), "ajustesinventario-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function ajustesinventario_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'AjustesInventario')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('ajustesinventario');
    }
}
