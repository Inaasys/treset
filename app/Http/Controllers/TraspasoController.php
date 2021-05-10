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
use App\Exports\TraspasosExport;
use App\Traspaso;
use App\TraspasoDetalle;
use App\Serie;
use App\Almacen;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Cliente;
use App\Configuracion_Tabla;
use App\VistaTraspaso;

class TraspasoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Traspasos')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function traspasos(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'Traspasos');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('traspasos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('traspasos_exportar_excel');
        $rutacreardocumento = route('traspasos_generar_pdfs');
        return view('registros.traspasos.traspasos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }

    public function traspasos_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            $data = VistaTraspaso::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Traspaso .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Traspaso .'\')"><i class="material-icons">cancel</i></div> ';
                        $operaciones =  $botoncambios.$botonbajas;
                        return $operaciones;
                    })
                    ->addColumn('subtotal', function($data){ return $data->SubTotal; })
                    ->addColumn('iva', function($data){ return $data->Iva; })
                    ->addColumn('total', function($data){ return $data->Total; })
                    ->addColumn('importe', function($data){ return $data->Importe; })
                    ->addColumn('descuento', function($data){ return $data->Descuento; })
                    ->addColumn('costo', function($data){ return $data->Costo; })
                    ->addColumn('comision', function($data){ return $data->Comision; })
                    ->addColumn('utilidad', function($data){ return $data->Utilidad; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimo folio
    public function traspasos_obtener_ultimo_folio(){
        $folio = Helpers::ultimofoliotablamodulos('App\Traspaso');
        return response()->json($folio);
    }

    //obtener almacenes
    public function traspasos_obtener_almacenes(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->where('Numero', '<>', $request->numeroalmacena)->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacen('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener alamcenes foraneos
    public function traspasos_obtener_almacenes_foraneos(Request $request){
        if($request->ajax()){
            $data = Almacen::where('Status', 'ALTA')->where('Numero', '<>', $request->numeroalmacende)->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaralmacenforaneo('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener ordenes de trabajo
    public function traspasos_obtener_ordenes_trabajo(Request $request){
        if($request->ajax()){
            $data = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordentrabajo(\''.$data->Orden.'\',\''.Helpers::formatoinputdate($data->Fecha).'\',\''.$data->Cliente.'\',\''.$data->Tipo.'\',\''.$data->Unidad.'\',\''.$data->StatusOrden.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener productos
    public function traspasos_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacende = $request->numeroalmacende;
            $tipooperacion = $request->tipooperacion;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from Existencias group by codigo) as e"),
                function($join){
                    $join->on("e.codigo","=","t.codigo");
                })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status', 't.Unidad AS Unidad', 't.Impuesto AS Impuesto')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacende, $tipooperacion){
                        //obtener existencias del codigo en el almacen seleccionado
                        $ContarExistencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacende)->count();
                        if($ContarExistencia > 0){
                            $Existencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacende)->first();
                            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                        }else{
                            $Existencias = 0;
                        }
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($Existencias).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
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
                    ->rawColumns(['operaciones','Costo','Existencias','SubTotal'])
                    ->make(true);
        } 
    }

    //obtener existencias
    public function traspasos_obtener_existencias_partida(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacende)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacende)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //obtener exstencias almacen foraneo
    public function traspasos_obtener_existencias_almacen_foraneo(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacena)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacena)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //guardar
    public function traspasos_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\Traspaso');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $traspaso = $folio.'-'.$request->serie;
        $Traspaso = new Traspaso;
        $Traspaso->Traspaso=$traspaso;
        $Traspaso->Serie=$request->serie;
        $Traspaso->Folio=$folio;
        $Traspaso->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Traspaso->De=$request->numeroalmacende;
        $Traspaso->A=$request->numeroalmacena;
        $Traspaso->Referencia=$request->referencia;
        $Traspaso->Orden=$request->orden;
        $Traspaso->Importe=$request->importe;
        $Traspaso->Descuento=$request->descuento;
        $Traspaso->SubTotal=$request->subtotal;
        $Traspaso->Iva=$request->iva;
        $Traspaso->Total=$request->total;
        $Traspaso->Costo=$request->costo;
        $Traspaso->Utilidad=$request->utilidad;
        $Traspaso->Obs=$request->observaciones;
        $Traspaso->Status="APLICADO";
        $Traspaso->Usuario=Auth::user()->user;
        $Traspaso->Periodo=$request->periodohoy;
        $Traspaso->save();
        //modificar totales orden trabajo
        if($request->orden != ""){
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $request->orden)->first();
            OrdenTrabajo::where('Orden', $request->orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe + $request->importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento + $request->descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal + $request->subtotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva + $request->iva,
                            'Total' => $OrdenTrabajoAnterior->Total + $request->total,
                            'Costo' => $OrdenTrabajoAnterior->Costo + $request->costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad + $request->utilidad
                        ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $traspaso;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "APLICADO";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $TraspasoDetalle=new TraspasoDetalle;
            $TraspasoDetalle->Traspaso = $traspaso;
            $TraspasoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $TraspasoDetalle->Codigo = $codigoproductopartida;
            $TraspasoDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $TraspasoDetalle->Unidad = $request->unidadproductopartida [$key];
            $TraspasoDetalle->Cantidad =  $request->cantidadpartida [$key];
            $TraspasoDetalle->Precio =  $request->preciopartida [$key];
            $TraspasoDetalle->Importe =  $request->importepartida [$key];
            $TraspasoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $TraspasoDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $TraspasoDetalle->SubTotal =  $request->subtotalpartida [$key];
            $TraspasoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $TraspasoDetalle->Iva =  $request->ivapesospartida [$key];
            $TraspasoDetalle->Total =  $request->totalpesospartida [$key];
            $TraspasoDetalle->Costo =  $request->costopartida [$key];
            $TraspasoDetalle->CostoTotal =  $request->costototalpartida [$key];
            $TraspasoDetalle->Utilidad =  $request->utilidadpartida [$key];
            $TraspasoDetalle->Moneda =  $request->monedapartida [$key];
            $TraspasoDetalle->Item = $item;
            $TraspasoDetalle->save();
            //restar existencias del almacen que se traspaso
            $ContarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->count();
            if($ContarExistenciaAlmacenDe > 0){
                $ExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacenDe
                            ]);
            }
            //si el traspaso sera a otro almacen
            if($request->numeroalmacena > 0){
                //agregar existencias al almacen al que se traspaso
                $ContarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->count();
                if($ContarExistenciaAlmacenA > 0){
                    $ExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacenA
                                ]);
                }else{
                    $ExistenciaAlmacenA = new Existencia;
                    $ExistenciaAlmacenA->Codigo = $codigoproductopartida;
                    $ExistenciaAlmacenA->Almacen = $request->numeroalmacena;
                    $ExistenciaAlmacenA->Existencias = $request->cantidadpartida [$key];
                    $ExistenciaAlmacenA->save();
                }
            }
            //si el traspaso sera para una orden de trabajo
            if($request->orden != ""){
                $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->orden)->first();
                $contardetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->orden)->count();
                if($contardetallesordentrabajo > 0){
                    $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->orden)->orderBy('Partida', 'DESC')->take(1)->get();
                    $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                }else{
                    $UltimaPartida = 1;
                }
                $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                $OrdenTrabajoDetalle->Orden=$request->orden;
                $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                $OrdenTrabajoDetalle->Descripcion=$request->descripcionproductopartida [$key];
                $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                $OrdenTrabajoDetalle->Iva=$request->ivapesospartida [$key];
                $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                $OrdenTrabajoDetalle->Costo=$request->costopartida [$key];
                $OrdenTrabajoDetalle->CostoTotal=$request->costototalpartida [$key];
                $OrdenTrabajoDetalle->Utilidad=$request->utilidadpartida [$key];
                $OrdenTrabajoDetalle->Departamento="REFACCIONES";
                $OrdenTrabajoDetalle->Cargo="REFACCIONES";
                $OrdenTrabajoDetalle->Traspaso=$traspaso;
                $OrdenTrabajoDetalle->Item=$item;
                $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                //$OrdenTrabajoDetalle->Facturar="S";
                $OrdenTrabajoDetalle->Almacen=$request->numeroalmacende;
                $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                $OrdenTrabajoDetalle->save();
                $UltimaPartida++;
            }
            $item++;
        }
        return response()->json($Traspaso);
    }

    //verificar baja
    public function traspasos_verificar_baja(Request $request){
        $Traspaso = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
        $errores = '';
        if($Traspaso->A > 0){
            $detalles = TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)->get();
            foreach($detalles as $detalle){
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->count();
                if($ContarExistenciaAlmacen > 0){
                    $Existencia = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->first();
                    $existencias = $Existencia->Existencias;
                }else{
                    $existencias = 0;
                }
                if($detalle->Cantidad > $existencias){
                    $errores = $errores.'Error el traspaso no se puede cancelar, no hay existencias suficientes en el almacen:'.$Traspaso->A.' para el código:'.$detalle->Codigo.'<br>';
                }
            }
        }else if($Traspaso->Orden != ""){
            $OrdenTrabajo = OrdenTrabajo::where('Orden', $Traspaso->Orden)->first();
            if($OrdenTrabajo->Status != "ABIERTA"){
                $errores = $errores.'Error el traspaso no se puede cancelar, porque la Orden de Trabajo:'.$Traspaso->Orden.' se encuentra en Status TERMINADA o FACTURADA<br>';
            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($Traspaso->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $Traspaso->Status
        );
        return response()->json($data);
    }

    //bajas
    public function traspasos_alta_o_baja(Request $request){
        $Traspaso = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
        //modificar totales orden trabajo
        if($Traspaso->Orden != ""){
            $TraspasoAnterior = Traspaso::where('Traspaso', $request->traspasodesactivar)->first();
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)->first();
            OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe - $TraspasoAnterior->Importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento - $TraspasoAnterior->Descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $TraspasoAnterior->SubTotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva - $TraspasoAnterior->Iva,
                            'Total' => $OrdenTrabajoAnterior->Total - $TraspasoAnterior->Total,
                            'Costo' => $OrdenTrabajoAnterior->Costo - $TraspasoAnterior->Costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad - $TraspasoAnterior->Utilidad
                        ]);
        }
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Traspaso::where('Traspaso', $request->traspasodesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Costo' => '0.000000',
                    'Utilidad' => '0.000000'
                ]);
        $detalles = TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)->get();
        foreach($detalles as $detalle){
            //sumar existencias al almacen que realizo el traspaso
            $ExistenciaAlmacenDe = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->De)->first();
            $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias+$detalle->Cantidad;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Traspaso->De)
                        ->update([
                            'Existencias' => $ExistenciaNuevaAlmacenDe
                        ]);
            if($Traspaso->A > 0){
                //restar existencias al almacen que solicito el traspaso
                $ExistenciaAlmacenA = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Traspaso->A)->first();
                $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias-$detalle->Cantidad;
                Existencia::where('Codigo', $detalle->Codigo)
                            ->where('Almacen', $Traspaso->A)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacenA
                            ]);
            }
            if($Traspaso->Orden != ""){
                $eliminarrefacciones = OrdenTrabajoDetalle::where('Traspaso', $request->traspasodesactivar)->where('Codigo', $detalle->Codigo)->forceDelete();
            }
            //colocar en ceros cantidades
            TraspasoDetalle::where('Traspaso', $request->traspasodesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'Total' => '0.000000',
                                'CostoTotal' => '0.000000',
                                'Utilidad' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $request->traspasodesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Traspaso->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Traspaso);
    }

    //obtener registro
    public function traspasos_obtener_traspaso(Request $request){
        $traspaso = Traspaso::where('Traspaso', $request->traspasomodificar)->first();
        $almacende = Almacen::where('Numero', $traspaso->De)->first();
        $almacena="";
        $ordentrabajo="";
        $cliente="";
        $fechaorden="";
        if($traspaso->A > 0){
            $almacena = Almacen::where('Numero', $traspaso->A)->first();
        }else if($traspaso->Orden != ""){
            $ordentrabajo = OrdenTrabajo::where('Orden', $traspaso->Orden)->first();
            $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
            $fechaorden = Helpers::formatoinputdate($ordentrabajo->Fecha);
        }
        //detalles
        $detallestraspaso= TraspasoDetalle::where('Traspaso', $request->traspasomodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallestraspaso = TraspasoDetalle::where('Traspaso', $request->traspasomodificar)->count();
        if($numerodetallestraspaso > 0){
            $filasdetallestraspaso = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallestraspaso as $dt){
                $producto = Producto::where('Codigo', $dt->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $dt->Codigo)->where('Almacen', $traspaso->De)->first();
                $parsleymax = $Existencia->Existencias+$dt->Cantidad;
                $filasdetallestraspaso= $filasdetallestraspaso.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dt->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dt->Codigo.'" readonly>'.$dt->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.$dt->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dt->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dt->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dt->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$parsleymax.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dt->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dt->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallestraspaso = '';
        } 
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($traspaso->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($traspaso->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($traspaso->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }        
        $data = array(
            "traspaso" => $traspaso,
            "almacende" => $almacende,
            "almacena" => $almacena,
            "ordentrabajo" => $ordentrabajo,
            "cliente" => $cliente,
            "filasdetallestraspaso" => $filasdetallestraspaso,
            "numerodetallestraspaso" => $numerodetallestraspaso,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdate($traspaso->Fecha),
            "fechaorden" => $fechaorden,
            "importe" => Helpers::convertirvalorcorrecto($traspaso->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($traspaso->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($traspaso->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($traspaso->Iva),
            "total" => Helpers::convertirvalorcorrecto($traspaso->Total),
            "costo" => Helpers::convertirvalorcorrecto($traspaso->Costo),
            "utilidad" => Helpers::convertirvalorcorrecto($traspaso->Utilidad),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //guardar modificacion 
    public function traspasos_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
        $traspaso = $request->folio.'-'.$request->serie;
        $Traspaso = Traspaso::where('Traspaso', $traspaso)->first();
        //modificar totales orden trabajo IMPORTANTE QUE ESTE AQUI
        if($Traspaso->Orden != ""){
            $TraspasoAnterior = Traspaso::where('Traspaso', $traspaso)->first();
            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)->first();
            OrdenTrabajo::where('Orden', $TraspasoAnterior->Orden)
                        ->update([
                            'Importe' => $OrdenTrabajoAnterior->Importe - $TraspasoAnterior->Importe + $request->importe,
                            'Descuento' => $OrdenTrabajoAnterior->Descuento - $TraspasoAnterior->Descuento + $request->descuento,
                            'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $TraspasoAnterior->SubTotal + $request->subtotal,
                            'Iva' => $OrdenTrabajoAnterior->Iva - $TraspasoAnterior->Iva + $request->iva,
                            'Total' => $OrdenTrabajoAnterior->Total - $TraspasoAnterior->Total + $request->total,
                            'Costo' => $OrdenTrabajoAnterior->Costo - $TraspasoAnterior->Costo + $request->costo,
                            'Utilidad' => $OrdenTrabajoAnterior->Utilidad - $TraspasoAnterior->Utilidad + $request->utilidad
                        ]);            
        }
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesTraspasoAnterior = Array();
        $DetallesTraspasoAnterior = TraspasoDetalle::where('Traspaso', $traspaso)->get();
        foreach($DetallesTraspasoAnterior as $detalle){
            //array_push($ArrayDetallesTraspasoAnterior, $detalle->Codigo);
            array_push($ArrayDetallesTraspasoAnterior, $detalle->Traspaso.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesTraspasoNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            //array_push($ArrayDetallesTraspasoNuevo, $nuevocodigo);
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesTraspasoNuevo, $traspaso.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesTraspasoAnterior, $ArrayDetallesTraspasoNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalletraspaso = TraspasoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacenDe = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacende)->first();
                $SumarExistenciaNuevaAlmacenDe = $SumarExistenciaAlmacenDe->Existencias + $detalletraspaso->Cantidad;
                Existencia::where('Codigo', $explode_d[1])
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacenDe
                            ]);
                //si el traspaso se realizo a otro almacen
                if($Traspaso->A > 0){
                    //restar existencias a almacen foraneo
                    $RestarExistenciasAlmacenA = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacena)->first();
                    $RestarExistenciaNuevaAlmacenA = $RestarExistenciasAlmacenA->Existencias - $detalletraspaso->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $RestarExistenciaNuevaAlmacenA
                                ]);
                }
                //eliminar detalle del traspaso eliminado
                $eliminardetalletraspaso = TraspasoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                if($Traspaso->Orden != ""){
                    $OrdenTrabajoDetalle = OrdenTrabajoDetalle::where('Traspaso', $explode_d[0])->where('Codigo', $explode_d[1])->forceDelete();
                }
            }
        }
        //modificar traspaso
        Traspaso::where('Traspaso', $traspaso)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Referencia' => $request->referencia,
            'Obs' => $request->observaciones,
            'Importe' => $request->importe,
            'Descuento' => $request->descuento,
            'SubTotal' => $request->subtotal,
            'Iva' => $request->iva,
            'Total' => $request->total,
            'Costo' => $request->costo,
            'Utilidad' => $request->utilidad
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "TRASPASOS";
        $BitacoraDocumento->Movimiento = $traspaso;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Traspaso->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        $item = TraspasoDetalle::select('Item')->where('Traspaso', $traspaso)->orderBy('Item', 'DESC')->take(1)->get();
        $ultimoitem = $item[0]->Item+1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
            if($request->agregadoen [$key] == 'modificacion'){
                $TraspasoDetalle=new TraspasoDetalle;
                $TraspasoDetalle->Traspaso = $traspaso;
                $TraspasoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $TraspasoDetalle->Codigo = $codigoproductopartida;
                $TraspasoDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $TraspasoDetalle->Unidad = $request->unidadproductopartida [$key];
                $TraspasoDetalle->Cantidad =  $request->cantidadpartida [$key];
                $TraspasoDetalle->Precio =  $request->preciopartida [$key];
                $TraspasoDetalle->Importe =  $request->importepartida [$key];
                $TraspasoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $TraspasoDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $TraspasoDetalle->SubTotal =  $request->subtotalpartida [$key];
                $TraspasoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $TraspasoDetalle->Iva =  $request->ivapesospartida [$key];
                $TraspasoDetalle->Total =  $request->totalpesospartida [$key];
                $TraspasoDetalle->Costo =  $request->costopartida [$key];
                $TraspasoDetalle->CostoTotal =  $request->costototalpartida [$key];
                $TraspasoDetalle->Utilidad =  $request->utilidadpartida [$key];
                $TraspasoDetalle->Moneda =  $request->monedapartida [$key];
                $TraspasoDetalle->Item = $ultimoitem;
                $TraspasoDetalle->save();
                //restar existencias del almacen que se traspaso
                $ContarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->count();
                if($ContarExistenciaAlmacenDe > 0){
                    $ExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                    $ExistenciaNuevaAlmacenDe = $ExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacende)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacenDe
                                ]);
                }
                //si el traspaso sera a otro almacen
                if($request->numeroalmacena > 0){
                    //agregar existencias al almacen al que se traspaso
                    $ContarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->count();
                    if($ContarExistenciaAlmacenA > 0){
                        $ExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                        $ExistenciaNuevaAlmacenA = $ExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                        Existencia::where('Codigo', $codigoproductopartida)
                                    ->where('Almacen', $request->numeroalmacena)
                                    ->update([
                                        'Existencias' => $ExistenciaNuevaAlmacenA
                                    ]);
                    }else{
                        $ExistenciaAlmacenA = new Existencia;
                        $ExistenciaAlmacenA->Codigo = $codigoproductopartida;
                        $ExistenciaAlmacenA->Almacen = $request->numeroalmacena;
                        $ExistenciaAlmacenA->Existencias = $request->cantidadpartida [$key];
                        $ExistenciaAlmacenA->save();
                    }
                }
                //si el traspaso sera para una orden de trabajo
                if($request->orden != ""){
                    $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->orden)->first();
                    $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->orden)->orderBy('Partida', 'DESC')->take(1)->get();
                    $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                    $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                    $OrdenTrabajoDetalle->Orden=$request->orden;
                    $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                    $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                    $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                    $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                    $OrdenTrabajoDetalle->Descripcion=$request->descripcionproductopartida [$key];
                    $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                    $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                    $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                    $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                    $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                    $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                    $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                    $OrdenTrabajoDetalle->Iva=$request->ivapesospartida [$key];
                    $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                    $OrdenTrabajoDetalle->Costo=$request->costopartida [$key];
                    $OrdenTrabajoDetalle->CostoTotal=$request->costototalpartida [$key];
                    $OrdenTrabajoDetalle->Utilidad=$request->utilidadpartida [$key];
                    $OrdenTrabajoDetalle->Departamento="REFACCIONES";
                    $OrdenTrabajoDetalle->Cargo="REFACCIONES";
                    $OrdenTrabajoDetalle->Traspaso=$traspaso;
                    $OrdenTrabajoDetalle->Item=$ultimoitem;
                    $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                    //$OrdenTrabajoDetalle->Facturar="S";
                    $OrdenTrabajoDetalle->Almacen=$request->numeroalmacende;
                    $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                    $OrdenTrabajoDetalle->save();
                    $UltimaPartida++;
                }
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                TraspasoDetalle::where('Traspaso', $traspaso)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Cantidad' =>  $request->cantidadpartida [$key],
                    'Precio' =>  $request->preciopartida [$key],
                    'Importe' =>  $request->importepartida [$key],
                    'Dcto' =>  $request->descuentoporcentajepartida [$key],
                    'Descuento' =>  $request->descuentopesospartida  [$key],
                    'SubTotal' =>  $request->subtotalpartida [$key],
                    'Impuesto' =>  $request->ivaporcentajepartida [$key],
                    'Iva' =>  $request->ivapesospartida [$key],
                    'Total' =>  $request->totalpesospartida [$key],
                    'Costo' =>  $request->costopartida [$key],
                    'CostoTotal' =>  $request->costototalpartida [$key],
                    'Utilidad' =>  $request->utilidadpartida [$key],
                    'Moneda' =>  $request->monedapartida [$key]
                ]);
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $SumarExistenciaNuevaAlmacenDe = $SumarExistenciaAlmacenDe->Existencias + $request->cantidadpartidadb [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacenDe
                            ]);
                //restar existencias a almacen principal
                $RestarExistenciaAlmacenDe = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacende)->first();
                $RestarExistenciaNuevaAlmacenDe = $RestarExistenciaAlmacenDe->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacende)
                            ->update([
                                'Existencias' => $RestarExistenciaNuevaAlmacenDe
                            ]);
                //si el traspaso se realizo a otro almacen
                if($Traspaso->A > 0){
                    //restar existencias a almacen foraneo
                    $RestarExistenciasAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $RestarExistenciaNuevaAlmacenA = $RestarExistenciasAlmacenA->Existencias - $request->cantidadpartidadb [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $RestarExistenciaNuevaAlmacenA
                                ]);
                    //sumar existencias a almacen foraneo
                    $SumarExistenciaAlmacenA = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacena)->first();
                    $SumarExistenciaNuevaAlmacenA = $SumarExistenciaAlmacenA->Existencias + $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacena)
                                ->update([
                                    'Existencias' => $SumarExistenciaNuevaAlmacenA
                                ]);
                }
                //si el traspaso se realizo a una orden de trabajo se modifican los detalles de la orden de trabajo
                if($Traspaso->Orden != ""){
                    $PrimerPartidaTraspasoOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $Traspaso->Orden)->where('Traspaso', $traspaso)->orderBy('Partida', 'ASC')->take(1)->get();
                    $Partida = $PrimerPartidaTraspasoOrdenTrabajoDetalle[0]->Partida;
                    OrdenTrabajoDetalle::where('Traspaso', $traspaso)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Cantidad' => $request->cantidadpartida [$key],
                        'Precio' => $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida  [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->ivapesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'CostoTotal' => $request->costototalpartida [$key],
                        'Utilidad' => $request->utilidadpartida [$key]
                    ]);
                    $Partida++;
                }
            }
        }
        return response()->json($Traspaso);
    }

    //buscar folio
    public function traspasos_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = Traspaso::where('Traspaso', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Traspaso .'\')"><i class="material-icons">done</i></div> ';
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
    public function traspasos_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $traspasos = Traspaso::whereIn('Traspaso', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $traspasos = Traspaso::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($traspasos as $t){
            $traspasodetalle = TraspasoDetalle::where('Traspaso', $t->Traspaso)->get();
            $datadetalle=array();
            foreach($traspasodetalle as $td){
                $producto = Producto::where('Codigo', $td->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($td->Cantidad),
                    "codigodetalle"=>$td->Codigo,
                    "descripciondetalle"=>$td->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($td->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($td->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($td->SubTotal)
                );
            } 
            $almacende = Almacen::where('Numero', $t->De)->first();
            $almacena = '';
            $orden = '';
            if($t->A > 0){
                $almacenforaneo = Almacen::where('Numero', $t->A)->first();
                $almacena = $almacenforaneo->Nombre.' ('.$almacenforaneo->Numero.')';
            }
            $data[]=array(
                      "traspaso"=>$t,
                      "descuentotraspaso"=>Helpers::convertirvalorcorrecto($t->Descuento),
                      "subtotaltraspaso"=>Helpers::convertirvalorcorrecto($t->SubTotal),
                      "ivatraspaso"=>Helpers::convertirvalorcorrecto($t->Iva),
                      "totaltraspaso"=>Helpers::convertirvalorcorrecto($t->Total),
                      "almacende" => $almacende,
                      "almacena" => $almacena,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.traspasos.formato_pdf_traspasos', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 5)
        ->setOption('margin-right', 5)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //exportar excel
    public function traspasos_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new TraspasosExport($this->campos_consulta,$request->periodo), "traspasos-".$request->periodo.".xlsx");   
  
    }

    //guardar configuracion tabla
    public function traspasos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'Traspasos')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('traspasos');
    }
    
}
