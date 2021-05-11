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
use App\Exports\RemisionesExport;
use App\Remision;
use App\RemisionDetalle;
use App\Factura;
use App\FacturaDetalle;
use App\Serie;
use App\Almacen;
use App\Cliente;
use App\Agente;
use App\TipoCliente;
use App\TipoUnidad;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaRemision;
use App\Cotizacion;
use App\CotizacionDetalle;

class RemisionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Remisiones')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function remisiones(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'Remisiones');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('remisiones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('remisiones_exportar_excel');
        $rutacreardocumento = route('remisiones_generar_pdfs');
        return view('registros.remisiones.remisiones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }

    //obtener registros tabla
    public function remisiones_obtener(Request $request){
        if($request->ajax()){
            $periodo = $request->periodo;
            $data = VistaRemision::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Remision .'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Remision .'\')"><i class="material-icons">cancel</i></div> ';
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
                    ->addColumn('tipocambio', function($data){ return $data->TipoCambio; })
                    ->addColumn('supago', function($data){ return $data->SuPago; })
                    ->addColumn('enefectivo', function($data){ return $data->EnEfectivo; })
                    ->addColumn('entarjetas', function($data){ return $data->EnTarjetas; })
                    ->addColumn('envales', function($data){ return $data->EnVales; })
                    ->addColumn('encheque', function($data){ return $data->EnCheque; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener ultimo folio
    public function remisiones_obtener_ultimo_folio(){
        $folio = Helpers::ultimofoliotablamodulos('App\Remision');
        return response()->json($folio);
    }

    //obtener clientes
    public function remisiones_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $contaragente = Agente::where('Numero', $data->Agente)->count();
                        $nombreagente = '';
                        $numeroagente = '';
                        if($contaragente > 0){
                            $agente = Agente::where('Numero', $data->Agente)->first();
                            $nombreagente = $agente->Nombre;
                            $numeroagente = $agente->Numero;
                        } 
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\',\''.Helpers::convertirvalorcorrecto($data->Credito).'\',\''.Helpers::convertirvalorcorrecto($data->Saldo).'\',\''.$numeroagente .'\',\''.$nombreagente .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener oagentes
    public function remisiones_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }

    //obtener almacenes
    public function remisiones_obtener_almacenes(Request $request){
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

    //obtener tipos cliente
    public function remisiones_obtener_tipos_cliente(){
        $tipos_cliente = TipoCliente::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_cliente = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_cliente as $tipo){
            $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_cliente);
    }

    //obtener tipos unidad
    public function remisiones_obtener_tipos_unidad(){
        $tipos_unidad = TipoUnidad::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_unidad = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_unidad as $tipo){
            $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidad); 
    }

    //obtener prudoctos
    public function remisiones_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            $data = DB::table('Productos as t')
            ->leftJoin('Marcas as m', 'm.Numero', '=', 't.Marca')
            ->leftJoin(DB::raw("(select codigo, sum(existencias) as existencias from Existencias group by codigo) as e"),
                function($join){
                    $join->on("e.codigo","=","t.codigo");
                })
            ->select('t.Codigo as Codigo', 't.Producto as Producto', 't.Ubicacion as Ubicacion', 'e.Existencias as Existencias', 't.Costo as Costo', 't.SubTotal as SubTotal', 't.Marca as Marca', 't.Status as Status', 't.Unidad AS Unidad', 't.Impuesto AS Impuesto', 't.Insumo AS Insumo', 't.ClaveProducto AS ClaveProducto', 't.ClaveUnidad AS ClaveUnidad', 't.CostoDeLista AS CostoDeLista')
            ->where('t.Codigo', 'like', '%' . $codigoabuscar . '%')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion){
                        //obtener existencias del codigo en el almacen seleccionado
                        $ContarExistencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacen)->count();
                        if($ContarExistencia > 0){
                            $Existencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacen)->first();
                            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                        }else{
                            $Existencias = 0;
                        }
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    public function remisiones_obtener_existencias_almacen(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }

    //guardar
    public function remisiones_guardar(Request $request){
        ini_set('max_input_vars','10000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofoliotablamodulos('App\Remision');
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $remision = $folio.'-'.$request->serie;
        $Remision = new Remision;
        $Remision->Remision=$remision;
        $Remision->Serie=$request->serie;
        $Remision->Folio=$folio;
        $Remision->Cliente=$request->numerocliente;
        $Remision->Agente=$request->numeroagente;
        $Remision->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Remision->Plazo=$request->plazo;
        $Remision->Tipo=$request->tipo;
        $Remision->Unidad=$request->unidad;
        $Remision->Pedido=$request->pedido;
        $Remision->Solicita=$request->solicitadopor;
        $Remision->Referencia=$request->referencia;
        $Remision->Destino=$request->destinodelpedido;
        $Remision->Almacen=$request->numeroalmacen;
        $Remision->Os=$request->ordenservicio;
        $Remision->Eq=$request->equipo;
        $Remision->Rq=$request->requisicion;
        $Remision->Importe=$request->importe;
        $Remision->Descuento=$request->descuento;
        $Remision->SubTotal=$request->subtotal;
        $Remision->Iva=$request->iva;
        $Remision->Total=$request->total;
        $Remision->Costo=$request->costo;
        $Remision->Comision=$request->comision;
        $Remision->Utilidad=$request->utilidad;
        $Remision->Obs=$request->observaciones;
        $Remision->Hora=Helpers::fecha_exacta_accion_datetimestring();
        $Remision->Status="POR FACTURAR";
        $Remision->Usuario=Auth::user()->user;
        $Remision->Periodo=$request->periodohoy;
        $Remision->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $remision;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR FACTURAR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $RemisionDetalle=new RemisionDetalle;
            $RemisionDetalle->Remision = $remision;
            $RemisionDetalle->Cliente = $request->numerocliente;
            $RemisionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $RemisionDetalle->Codigo = $codigoproductopartida;
            $RemisionDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $RemisionDetalle->Unidad = $request->unidadproductopartida [$key];
            $RemisionDetalle->Cantidad =  $request->cantidadpartida [$key];
            $RemisionDetalle->Precio =  $request->preciopartida [$key];
            $RemisionDetalle->Importe =  $request->importepartida [$key];
            $RemisionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $RemisionDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $RemisionDetalle->SubTotal =  $request->subtotalpartida [$key];
            $RemisionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $RemisionDetalle->Iva =  $request->ivapesospartida [$key];
            $RemisionDetalle->Total =  $request->totalpesospartida [$key];
            $RemisionDetalle->Costo =  $request->costopartida [$key];
            $RemisionDetalle->CostoTotal =  $request->costototalpartida [$key];
            $RemisionDetalle->Com =  $request->comisionporcentajepartida [$key];
            $RemisionDetalle->Comision =  $request->comisionespesospartida [$key];
            $RemisionDetalle->Utilidad =  $request->utilidadpartida [$key];
            $RemisionDetalle->Moneda =  $request->monedapartida [$key];
            $RemisionDetalle->CostoDeLista =  $request->costolistapartida [$key];
            $RemisionDetalle->Insumo =  $request->insumopartida [$key];
            $RemisionDetalle->InteresMeses =  $request->mesespartida [$key];
            $RemisionDetalle->InteresTasa =  $request->tasainterespartida  [$key];
            $RemisionDetalle->InteresMonto =  $request->montointerespartida  [$key];
            $RemisionDetalle->Item = $item;
            $RemisionDetalle->save();
            //modificar fechaultimaventa y ultimocosto
            $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
            $Producto->{'Fecha Ultima Venta'} = Carbon::parse($request->fecha)->toDateTimeString();
            $Producto->{'Ultima Venta'} = $request->preciopartida [$key];
            $Producto->save();
            //restar existencias del almacen 
            $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
            if($ContarExistenciaAlmacen > 0){
                $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $ExistenciaNuevaAlmacen
                            ]);
            }
            $item++;
        }
        return response()->json($Remision);
    }

    //verificar baja
    public function remisiones_verificar_baja(Request $request){
        $Remision = Remision::where('Remision', $request->remisiondesactivar)->first();
        $ContarDetallesRemisionFacturados = FacturaDetalle::where('Remision', $request->remisiondesactivar)->count();
        $errores = '';
        if($ContarDetallesRemisionFacturados){
            $DetallesRemisionFacturados = FacturaDetalle::where('Remision', $request->remisiondesactivar)->get();
            foreach($DetallesRemisionFacturados as $detalle){
                $errores = $errores.'Error la remisión no se puede cancelar, porque existen registros de remisiones en la factura No:'.$detalle->Factura.'<br>';
            }  
        }
        $errorescotizacion = '';
        $ContarRemisionCotizados = Cotizacion::where('num_remision', $request->remisiondesactivar)->where('status', 'ALTA')->count();
        if($ContarRemisionCotizados){
            $RemisionCotizados = Cotizacion::where('num_remision', $request->remisiondesactivar)->where('status', 'ALTA')->get();
            foreach($RemisionCotizados as $cot){
                $errorescotizacion = $errorescotizacion.'Error la remisión no se puede cancelar, porque existen registros de remisiones en la cotización No:'.$cot->cotizacion.'<br>';
            }  
        }
        $resultadofechas = Helpers::compararanoymesfechas($Remision->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'errorescotizacion' => $errorescotizacion,
            'Status' => $Remision->Status
        );
        return response()->json($data);
    }

    //bajas
    public function remisiones_alta_o_baja(Request $request){
        $Remision = Remision::where('Remision', $request->remisiondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Remision::where('Remision', $request->remisiondesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'Total' => '0.000000',
                    'Costo' => '0.000000',
                    'Comision' => '0.000000',
                    'Utilidad' => '0.000000'
                ]);
        $detalles = RemisionDetalle::where('Remision', $request->remisiondesactivar)->get();
        foreach($detalles as $detalle){
            //sumar existencias al almacen
            $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Remision->Almacen)->first();
            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias+$detalle->Cantidad;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Remision->Almacen)
                        ->update([
                            'Existencias' => $ExistenciaNuevaAlmacen
                        ]);
            //colocar en ceros cantidades
            RemisionDetalle::where('Remision', $request->remisiondesactivar)
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
                                'Com' => '0.000000',
                                'Comision' => '0.000000',
                                'Utilidad' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $request->remisiondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Remision->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Remision);
    }

    //obtener registro
    public function remisiones_obtener_remision(Request $request){
        $remision = Remision::where('Remision', $request->remisionmodificar)->first();
        $cliente = Cliente::where('Numero', $remision->Cliente)->first();
        $agente = Agente::where('Numero', $remision->Agente)->first();
        $almacen = Almacen::where('Numero', $remision->Almacen)->first();
        $tipos_cliente = TipoCliente::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_cliente = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_cliente as $tipo){
            if($tipo->Nombre == $remision->Tipo){
                $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."' selected>".$tipo->Nombre."</option>";
            }else{
                $select_tipos_cliente = $select_tipos_cliente."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
            }
        }
        $tipos_unidad = TipoUnidad::where('STATUS', 'ALTA')->orderBy('Numero', 'ASC')->get();
        $select_tipos_unidad = "<option selected disabled hidden>Selecciona...</option>";
        foreach($tipos_unidad as $tipo){
            if($tipo->Nombre == $remision->Unidad){
                $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."' selected>".$tipo->Nombre."</option>";
            }else{
                $select_tipos_unidad = $select_tipos_unidad."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
            }
        }
        //detalles
        $detallesremision= RemisionDetalle::where('Remision', $request->remisionmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesremision = RemisionDetalle::where('Remision', $request->remisionmodificar)->count();
        if($numerodetallesremision > 0){
            $filasdetallesremision = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesremision as $dr){
                $producto = Producto::where('Codigo', $dr->Codigo)->first();
                $Existencia = Existencia::where('Codigo', $dr->Codigo)->where('Almacen', $remision->Almacen)->first();
                $parsleymax = $Existencia->Existencias+$dr->Cantidad;
                $filasdetallesremision= $filasdetallesremision.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dr->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.$dr->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dr->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-existencias="'.$parsleymax.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Utilidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dr->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$dr->Insumo.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresMeses).'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresTasa).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto($dr->InteresMonto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesremision = '';
        }      
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($remision->Status != 'POR FACTURAR'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($remision->Status != 'POR FACTURAR'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($remision->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }  
        $data = array(
            "remision" => $remision,
            "cliente" => $cliente,
            "agente" => $agente,
            "almacen" => $almacen,
            "select_tipos_cliente" => $select_tipos_cliente,
            "select_tipos_unidad" => $select_tipos_unidad,
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
            "costo" => Helpers::convertirvalorcorrecto($remision->Costo),
            "utilidad" => Helpers::convertirvalorcorrecto($remision->Utilidad),
            "comision" => Helpers::convertirvalorcorrecto($remision->Comision),
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito),
            "saldo" => Helpers::convertirvalorcorrecto($cliente->Saldo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function remisiones_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
        $remision = $request->folio.'-'.$request->serie;
        $Remision = Remision::where('Remision', $remision)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesRemisionAnterior = Array();
        $DetallesRemisionAnterior = RemisionDetalle::where('Remision', $remision)->get();
        foreach($DetallesRemisionAnterior as $detalle){
            //array_push($ArrayDetallesRemisionAnterior, $detalle->Codigo);
            array_push($ArrayDetallesRemisionAnterior, $detalle->Remision.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesRemisionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            //array_push($ArrayDetallesRemisionNuevo, $nuevocodigo);
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesRemisionNuevo, $remision.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesRemisionAnterior, $ArrayDetallesRemisionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                $detalleremision = RemisionDetalle::where('Remision', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $detalleremision->Cantidad;
                Existencia::where('Codigo', $explode_d[1])
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacen
                            ]);

                //eliminar detalle de la remision eliminado
                $eliminardetalleremision = RemisionDetalle::where('Remision', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar remision
        Remision::where('Remision', $remision)
        ->update([
            'Cliente' => $request->numerocliente,
            'Agente' => $request->numeroagente,
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo' => $request->plazo,
            'Tipo' => $request->tipo,
            'Unidad' => $request->unidad,
            'Pedido' => $request->pedido,
            'Solicita' => $request->solicitadopor,
            'Referencia' => $request->referencia,
            'Destino' => $request->destinodelpedido,
            'Os' => $request->ordenservicio,
            'Eq' => $request->equipo,
            'Rq' => $request->requisicion,
            'Obs' => $request->observaciones,
            'Importe' => $request->importe,
            'Descuento' => $request->descuento,
            'SubTotal' => $request->subtotal,
            'Iva' => $request->iva,
            'Total' => $request->total,
            'Costo' => $request->costo,
            'Utilidad' => $request->utilidad,
            'Comision' => $request->comision
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REMISIONES";
        $BitacoraDocumento->Movimiento = $remision;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Remision->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $request->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
            if($request->agregadoen [$key] == 'modificacion'){
                $item = RemisionDetalle::select('Item')->where('Remision', $remision)->orderBy('Item', 'DESC')->take(1)->get();
                $ultimoitem = $item[0]->Item+1;
                $RemisionDetalle=new RemisionDetalle;
                $RemisionDetalle->Remision = $remision;
                $RemisionDetalle->Cliente = $request->numerocliente;
                $RemisionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $RemisionDetalle->Codigo = $codigoproductopartida;
                $RemisionDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $RemisionDetalle->Unidad = $request->unidadproductopartida [$key];
                $RemisionDetalle->Cantidad =  $request->cantidadpartida [$key];
                $RemisionDetalle->Precio =  $request->preciopartida [$key];
                $RemisionDetalle->Importe =  $request->importepartida [$key];
                $RemisionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $RemisionDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $RemisionDetalle->SubTotal =  $request->subtotalpartida [$key];
                $RemisionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $RemisionDetalle->Iva =  $request->ivapesospartida [$key];
                $RemisionDetalle->Total =  $request->totalpesospartida [$key];
                $RemisionDetalle->Costo =  $request->costopartida [$key];
                $RemisionDetalle->CostoTotal =  $request->costototalpartida [$key];
                $RemisionDetalle->Com =  $request->comisionporcentajepartida [$key];
                $RemisionDetalle->Comision =  $request->comisionespesospartida [$key];
                $RemisionDetalle->Utilidad =  $request->utilidadpartida [$key];
                $RemisionDetalle->Moneda =  $request->monedapartida [$key];
                $RemisionDetalle->CostoDeLista =  $request->costolistapartida [$key];
                $RemisionDetalle->Insumo =  $request->insumopartida [$key];
                $RemisionDetalle->InteresMeses =  $request->mesespartida [$key];
                $RemisionDetalle->InteresTasa =  $request->tasainterespartida  [$key];
                $RemisionDetalle->InteresMonto =  $request->montointerespartida  [$key];
                $RemisionDetalle->Item = $ultimoitem;
                $RemisionDetalle->save();
                //restar existencias del almacen 
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistenciaAlmacen > 0){
                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacen
                                ]);
                }
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                RemisionDetalle::where('Remision', $remision)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Cliente' => $request->numerocliente,
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Descripcion' => $request->descripcionproductopartida [$key],
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
                    'Com' =>  $request->comisionporcentajepartida [$key],
                    'Comision' =>  $request->comisionespesospartida [$key],
                    'Utilidad' =>  $request->utilidadpartida [$key],
                    'Moneda' =>  $request->monedapartida [$key],
                    'InteresMeses' =>  $request->mesespartida [$key],
                    'InteresTasa' =>  $request->tasainterespartida  [$key],
                    'InteresMonto' =>  $request->montointerespartida  [$key]
                ]);
                //sumar existencias a almacen principal
                $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartidadb [$key];
                Existencia::where('Codigo', $codigoproductopartida)
                            ->where('Almacen', $request->numeroalmacen)
                            ->update([
                                'Existencias' => $SumarExistenciaNuevaAlmacen
                            ]);
                //restar existencias del almacen 
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistenciaAlmacen > 0){
                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartida [$key];
                    Existencia::where('Codigo', $codigoproductopartida)
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => $ExistenciaNuevaAlmacen
                                ]);
                }
            }
        }
        return response()->json($Remision);
    }

    //buscar folio
    public function remisiones_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = Remision::where('Remision', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Remision .'\')"><i class="material-icons">done</i></div> ';
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
    public function remisiones_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $remisiones = Remision::whereIn('Remision', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $remisiones = Remision::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($remisiones as $r){
            $remisiondetalle = RemisionDetalle::where('Remision', $r->Remision)->get();
            $datadetalle=array();
            foreach($remisiondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($rd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($rd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $r->Cliente)->first();
            $agente = Agente::where('Numero', $r->Agente)->first();
            $data[]=array(
                      "remision"=>$r,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //exportar excel
    public function remisiones_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new RemisionesExport($this->campos_consulta,$request->periodo), "remisiones-".$request->periodo.".xlsx");   
    }

    //guardar configuracion tabla
    public function remisiones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'Remisiones')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('remisiones');
    }
    
}
