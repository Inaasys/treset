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
use App\Exports\OrdenesDeCompraExport;
use App\OrdenCompra;
use App\OrdenCompraDetalle;
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
use App\VistaOrdenCompra;
use App\VistaObtenerExistenciaProducto;
use Config;
use Mail;


class OrdenCompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeCompra')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function ordenes_compra(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('ordenes_compra_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('ordenes_compra_exportar_excel');
        $rutacreardocumento = route('ordenes_compra_generar_pdfs');
        return view('registros.ordenescompra.ordenescompra', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener todos los registros
    public function ordenes_compra_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaOrdenCompra::select($this->campos_consulta)->orderBy('Fecha', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Orden .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="autorizarordencompra(\''.$data->Orden .'\')">Autorizar</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Orden .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('ordenes_compra_generar_pdfs_indiv',$data->Orden).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Orden .'\')">Enviar Documento por Correo</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        /*
                        $botoncambios = '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Orden .'\')"><i class="material-icons">mode_edit</i></div> ';
                        $botonautorizar = '<div class="btn bg-green btn-xs waves-effect" data-toggle="tooltip" title="Autorizar" onclick="autorizarordencompra(\''.$data->Orden .'\')"><i class="material-icons">check</i></div> ';
                        $botonbajas = '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Orden .'\')"><i class="material-icons">cancel</i></div> ';
                        $botondocumentopdf = '<a href="'.route('ordenes_compra_generar_pdfs_indiv',$data->Orden).'" target="_blank"><div class="btn bg-blue-grey btn-xs waves-effect" data-toggle="tooltip" title="Generar Documento"><i class="material-icons">archive</i></div></a> ';
                        $botonenviaremail = '<div class="btn bg-brown btn-xs waves-effect" data-toggle="tooltip" title="Enviar Documento por Correo" onclick="enviardocumentoemail(\''.$data->Orden .'\')"><i class="material-icons">email</i></div> ';
                        $operaciones = $botoncambios.$botonautorizar.$botonbajas.$botondocumentopdf.$botonenviaremail;
                        */
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 
    //obtener series documento
    public function ordenes_compra_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'OrdenesDeCompra')->where('Usuario', Auth::user()->user)->get();
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
    public function ordenes_compra_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->Serie);
        return response()->json($folio);
    }
    //obtener el ultimo folio de la tabla
    public function ordenes_compra_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->serie);
        return response()->json($folio);
    }
    //obtener fecha date time actual
    public function ordenes_compra_obtener_fecha_actual_datetimelocal(Request $request){
        $fechadatetimelocal = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechadatetimelocal);
    }
    //obtener tipos ordenes de compra
    public function ordenes_compra_obtener_tipos_ordenes_compra(Request $request){
        switch ($request->tipoalta) {
            case "GASTOS":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'GASTOS')->get();
                break;
            case "TOT":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'TOT')->get();
                break;
            default:
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();

        } 
        $select_tipos_ordenes_compra = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener proveedores
    public function ordenes_compra_obtener_proveedores(Request $request){
        if($request->ajax()){
            $data = Proveedor::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener almacenes
    public function ordenes_compra_obtener_almacenes(Request $request){
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
    //obtener ordenes trabajo
    public function ordenes_compra_obtener_ordenes_trabajo(Request $request){
        if($request->ajax()){
            $data = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordentrabajo(\''.$data->Orden.'\',\''.Helpers::formatoinputdatetime($data->Fecha).'\',\''.$data->Cliente.'\',\''.$data->Tipo.'\',\''.$data->Unidad.'\',\''.$data->StatusOrden.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener orden trabajo por folio
    public function ordenes_compra_obtener_orden_trabajo_por_folio(Request $request){
        $orden = '';
        $existeorden = DB::table('Ordenes de Trabajo as ot')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                            ->where('ot.Status', 'ABIERTA')
                            ->where('ot.Orden', $request->ordentrabajo)
                            ->count();
        if($existeorden > 0){
            $orden = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->where('ot.Orden', $request->ordentrabajo)
                        ->get();
                        //dd($orden[0]);
            $orden = $orden[0]->Orden;
        }
        $data = array(
            'orden' => $orden
        );
        return response()->json($data); 
    }
    //obtener productos
    public function ordenes_compra_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $numeroalmacen = $request->numeroalmacen;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
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
    //obtener proveedor por numero
    public function ordenes_compra_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
            $plazo = $proveedor->Plazo;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo
        );
        return response()->json($data);
    }
    //obtener almacen por numero
    public function ordenes_compra_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
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
    //guardar en el módulo
    public function ordenes_compra_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\OrdenCompra',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $orden = $folio.'-'.$request->serie;
		$OrdenCompra = new OrdenCompra;
		$OrdenCompra->Orden=$orden;
		$OrdenCompra->Serie=$request->serie;
		$OrdenCompra->Folio=$request->folio;
		$OrdenCompra->Proveedor=$request->numeroproveedor;
        $OrdenCompra->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
		$OrdenCompra->Plazo=$request->plazo;
		$OrdenCompra->Almacen=$request->numeroalmacen;
		$OrdenCompra->Referencia=$request->referencia;
        $OrdenCompra->Tipo=$request->tipo;
        $OrdenCompra->Importe=$request->importe;
        $OrdenCompra->Descuento=$request->descuento;  
        $OrdenCompra->SubTotal=$request->subtotal;
        $OrdenCompra->Iva=$request->iva;
        $OrdenCompra->Total=$request->total;
        $OrdenCompra->Obs=$request->observaciones;
        $OrdenCompra->Status="POR SURTIR";
        $OrdenCompra->Usuario=Auth::user()->user;
        $OrdenCompra->Periodo=$this->periodohoy;
        $OrdenCompra->OrdenTrabajo=$request->ordentrabajo;
        $OrdenCompra->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR SURTIR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $OrdenCompraDetalle=new OrdenCompraDetalle;
            $OrdenCompraDetalle->Orden = $orden;
            $OrdenCompraDetalle->Proveedor = $request->numeroproveedor;
            $OrdenCompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $OrdenCompraDetalle->Codigo = $codigoproductopartida;
            $OrdenCompraDetalle->Descripcion = $request->nombreproductopartida [$key];
            $OrdenCompraDetalle->Unidad = $request->unidadproductopartida [$key];
            $OrdenCompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
            $OrdenCompraDetalle->Precio =  $request->preciopartida [$key];
            $OrdenCompraDetalle->Importe = $request->importepartida [$key];
            //$OrdenCompraDetalle->Costo = $request->total [$key];
            $OrdenCompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
            $OrdenCompraDetalle->Descuento = $request->descuentopesospartida [$key];
            $OrdenCompraDetalle->SubTotal = $request->subtotalpartida [$key];
            $OrdenCompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
            $OrdenCompraDetalle->Iva = $request->ivapesospartida [$key];
            $OrdenCompraDetalle->Total = $request->totalpesospartida [$key];
            $OrdenCompraDetalle->Surtir = $request->cantidadpartida  [$key];
            $OrdenCompraDetalle->Registro = 0;
            $OrdenCompraDetalle->Item = $item;
            $OrdenCompraDetalle->save();
            $item++;
        }
    	return response()->json($OrdenCompra); 
    }
    //autorizar una orden de compra
    public function ordenes_compra_autorizar(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordenautorizar)->first();
        $OrdenCompra->AutorizadoPor = Auth::user()->user; 
        $OrdenCompra->AutorizadoFecha = Helpers::fecha_exacta_accion_datetimestring();
        $OrdenCompra->save();
        return response()->json($OrdenCompra);
    }
    //verificar si la orden de compra ya fue utilizada en una compra
    public function ordenes_compra_verificar_uso_en_modulos(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordendesactivar)->first();
        $resultado = CompraDetalle::where('Orden', $request->ordendesactivar)->count();
        $numerocompra = 0;
        if($resultado > 0){
            $detallecompra = CompraDetalle::where('Orden', $request->ordendesactivar)->first();
            $numerocompra = $detallecompra->Compra;
        }
        $resultadofechas = Helpers::compararanoymesfechas($OrdenCompra->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'resultado' => $resultado,
            'numerocompra' => $numerocompra,
            'Status' => $OrdenCompra->Status
        );
        return response()->json($data);
    }
    //dar de baja orden de compra
    public function ordenes_compra_alta_o_baja(Request $request){
        $OrdenCompra = OrdenCompra::where('Orden', $request->ordendesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        OrdenCompra::where('Orden', $request->ordendesactivar)
        ->update([
            'MotivoBaja' => $MotivoBaja,
            'Status' => 'BAJA',
            'Importe' => '0.000000',
            'Descuento' => '0.000000',
            'SubTotal' => '0.000000',
            'Iva' => '0.000000',
            'Total' => '0.000000'
        ]);
        $detalles = OrdenCompraDetalle::where('Orden', $request->ordendesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            OrdenCompraDetalle::where('Orden', $request->ordendesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'Total' => '0.000000',
                                'Surtir' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $request->ordendesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenCompra->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($OrdenCompra);
    }
    //obtener datos de orden de compra
    public function ordenes_compra_obtener_orden_compra(Request $request){
        $ordencompra = OrdenCompra::where('Orden', $request->ordenmodificar)->first();
        $proveedor = Proveedor::where('Numero', $ordencompra->Proveedor)->first();
        $almacen = Almacen::where('Numero', $ordencompra->Almacen)->first();
        //saber si la modificacion es permitida
        $sumatotalcompras = 0;
        if($ordencompra->Status == 'POR SURTIR' || $ordencompra->Status == 'BACKORDER' && $ordencompra->AutorizadoPor == ''){
            $modificacionpermitida = 1;
            $readonly = '';  
        }else if($ordencompra->Status == 'BACKORDER'){
            $modificacionpermitida = 1;
            $readonly = '';  
            //traer los totales de las compras en las que la orden ya fue utilizada
            $compras = Compra::where('Orden',$request->ordenmodificar)->get();
            foreach($compras as $compra){
                $sumatotalcompras = $sumatotalcompras + $compra->Total;
            }
        }else{
            $modificacionpermitida = 0;
            $readonly = 'readonly="readonly"';
        }
        //detalles orden compra
        $detallesordencompra = OrdenCompraDetalle::where('Orden', $request->ordenmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesordencompra = OrdenCompraDetalle::where('Orden', $request->ordenmodificar)->count();
        if($numerodetallesordencompra > 0){
            $filasdetallesordencompra = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo = "modificacion";
            foreach($detallesordencompra as $doc){
                $cantidadyasurtidapartida = $doc->Cantidad - $doc->Surtir;
                //si la partida ya fue surtida no se puede modificar
                if($doc->Surtir == 0){
                    $readonly = 'readonly="readonly"';
                }else{
                    $readonly = ''; 
                }
                $filasdetallesordencompra= $filasdetallesordencompra.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilapreciosproductos('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$doc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$doc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$doc->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" value="'.$doc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$doc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$doc->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida"  name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Cantidad).'" data-parsley-min="'.Helpers::convertirvalorcorrecto($cantidadyasurtidapartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo .'\');formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadyasurtidapartida"  name="cantidadyasurtidapartida[]" value="'.Helpers::convertirvalorcorrecto($cantidadyasurtidapartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida"  name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo .'\');formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida"  name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida"  name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculardescuentopesospartida('.$contadorfilas.');formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida"  name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculardescuentoporcentajepartida('.$contadorfilas.');formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida"  name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida"  name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="calculartotalesfilasordencompra('.$contadorfilas.');formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida"  name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida"  name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesordencompra = '';
        }    
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($ordencompra->Status == 'SURTIDO' || $ordencompra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($ordencompra->Status == 'SURTIDO' || $ordencompra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($ordencompra->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    if($ordencompra->AutorizadoPor == ''){
                        $modificacionpermitida = 1;
                    }else{
                        $modificacionpermitida = 0;
                    }
                }
            }
        }     
        $data = array(
            "ordencompra" => $ordencompra,
            "filasdetallesordencompra" => $filasdetallesordencompra,
            "numerodetallesordencompra" => $numerodetallesordencompra,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "modificacionpermitida" => $modificacionpermitida,
            "proveedor" => $proveedor,
            "almacen" => $almacen,
            "fecha" => Helpers::formatoinputdatetime($ordencompra->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($ordencompra->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($ordencompra->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($ordencompra->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($ordencompra->Iva),
            "total" => Helpers::convertirvalorcorrecto($ordencompra->Total),
            "autorizadopor" => $ordencompra->AutorizadoPor,
            "sumatotalcompras" => Helpers::convertirvalorcorrecto($sumatotalcompras),
            "statusordencompra" => $ordencompra->Status
        );
        return response()->json($data);
    }
    //modificar datos orden de compra
    public function ordenes_compra_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $orden = $request->folio.'-'.$request->serie;
		$OrdenCompra = OrdenCompra::where('Orden', $orden)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
        //array partidas antes de modificacion
        $ArrayDetallesOrdenCompraAnterior = Array();
        $DetallesOrdenCompraAnterior = OrdenCompraDetalle::where('Orden', $orden)->get();
        foreach($DetallesOrdenCompraAnterior as $detalle){
            //array_push($ArrayDetallesOrdenCompraAnterior, $detalle->Codigo);
            array_push($ArrayDetallesOrdenCompraAnterior, $detalle->Orden.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesOrdenCompraNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            //array_push($ArrayDetallesOrdenCompraNuevo, $nuevocodigo);
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesOrdenCompraNuevo, $orden.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesOrdenCompraAnterior, $ArrayDetallesOrdenCompraNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle de la remision eliminado
                $eliminardetalleordencompra = OrdenCompraDetalle::where('Orden', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar orden compra
        OrdenCompra::where('Orden', $orden)
        ->update([
            'Proveedor'=>$request->numeroproveedor,
            'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo'=>$request->plazo,
            'Almacen'=>$request->numeroalmacen,
            'Referencia'=>$request->referencia,
            'Tipo'=>$request->tipo,
            'Importe'=>$request->importe,
            'Descuento'=>$request->descuento,  
            'SubTotal'=>$request->subtotal,
            'Iva'=>$request->iva,
            'Total'=>$request->total,
            'Obs'=>$request->observaciones,
            'OrdenTrabajo'=>$request->ordentrabajo
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "ORDENES DE COMPRA";
        $BitacoraDocumento->Movimiento = $orden;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $OrdenCompra->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $detallesporsurtir = 0;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles
            if($request->agregadoen [$key] == 'modificacion'){      
                $item = OrdenCompraDetalle::select('Item')->where('Orden', $orden)->orderBy('Item', 'DESC')->take(1)->get();
                $ultimoitem = $item[0]->Item+1;
                $OrdenCompraDetalle=new OrdenCompraDetalle;
                $OrdenCompraDetalle->Orden = $orden;
                $OrdenCompraDetalle->Proveedor = $request->numeroproveedor;
                $OrdenCompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $OrdenCompraDetalle->Codigo = $codigoproductopartida;
                $OrdenCompraDetalle->Descripcion = $request->nombreproductopartida [$key];
                $OrdenCompraDetalle->Unidad = $request->unidadproductopartida [$key];
                $OrdenCompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $OrdenCompraDetalle->Precio =  $request->preciopartida [$key];
                $OrdenCompraDetalle->Importe = $request->importepartida [$key];
                $OrdenCompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $OrdenCompraDetalle->Descuento = $request->descuentopesospartida [$key];
                $OrdenCompraDetalle->SubTotal = $request->subtotalpartida [$key];
                $OrdenCompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $OrdenCompraDetalle->Iva = $request->ivapesospartida [$key];
                $OrdenCompraDetalle->Total = $request->totalpesospartida [$key];
                $OrdenCompraDetalle->Surtir = $request->cantidadpartida  [$key];
                $OrdenCompraDetalle->Registro = 0;
                $OrdenCompraDetalle->Item = $ultimoitem;
                $OrdenCompraDetalle->save();
                $ultimoitem++;   
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                OrdenCompraDetalle::where('Orden', $orden)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Proveedor' => $request->numeroproveedor,
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Codigo' => $codigoproductopartida,
                    'Descripcion' => $request->nombreproductopartida [$key],
                    'Unidad' => $request->unidadproductopartida [$key],
                    'Cantidad' =>  $request->cantidadpartida  [$key],
                    'Precio' =>  $request->preciopartida [$key],
                    'Importe' => $request->importepartida [$key],
                    'Dcto' => $request->descuentoporcentajepartida [$key],
                    'Descuento' => $request->descuentopesospartida [$key],
                    'SubTotal' => $request->subtotalpartida [$key],
                    'Impuesto' => $request->ivaporcentajepartida [$key],
                    'Iva' => $request->ivapesospartida [$key],
                    'Total' => $request->totalpesospartida [$key],
                    'Surtir' => $request->porsurtirpartida [$key]
                ]);
            }
            //verificar si la partida ya esta surtida
            if($request->porsurtirpartida [$key] > 0){
                $detallesporsurtir++;//aun no se termina de surtir
            }
        }
        //Cerrar la orden de compra si todas sus partidas tienen cero en por surtir
        if($detallesporsurtir == 0){
            $OrdenCompra = OrdenCompra::where('Orden', $orden)->first();
            $OrdenCompra->Status='SURTIDO';
            $OrdenCompra->save();
        }
    	return response()->json($OrdenCompra);     
    }
    //buscar folio on key up
    public function ordenes_compra_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = OrdenCompra::where('Orden', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Orden .'\')"><i class="material-icons">done</i></div> ';
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
    //generacion de formato en PDF
    public function ordenes_compra_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $ordenescompra = OrdenCompra::whereIn('Orden', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $ordenescompra = OrdenCompra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenescompra as $oc){
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            } 
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
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
    public function ordenes_compra_generar_pdfs_indiv($documento){
        $ordenescompra = OrdenCompra::where('Orden', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenescompra as $oc){
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            } 
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
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
    public function ordenes_compra_obtener_datos_envio_email(Request $request){
        $ordencompra = OrdenCompra::where('Orden', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$ordencompra->Proveedor)->first();
        $data = array(
            'ordencompra' => $ordencompra,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function ordenes_compra_enviar_pdfs_email(Request $request){
        $ordenescompra = OrdenCompra::where('Orden', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($ordenescompra as $oc){
            $ordencompradetalle = OrdenCompraDetalle::where('Orden', $oc->Orden)->get();
            $datadetalle=array();
            foreach($ordencompradetalle as $ocd){
                $producto = Producto::where('Codigo', $ocd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ocd->Cantidad),
                    "codigodetalle"=>$ocd->Codigo,
                    "descripciondetalle"=>$ocd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ocd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($ocd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ocd->SubTotal)
                );
            } 
            $proveedor = Proveedor::where('Numero', $oc->Proveedor)->first();
            $data[]=array(
                      "ordencompra"=>$oc,
                      "descuentoordencompra"=>Helpers::convertirvalorcorrecto($oc->Descuento),
                      "subtotalordencompra"=>Helpers::convertirvalorcorrecto($oc->SubTotal),
                      "ivaordencompra"=>Helpers::convertirvalorcorrecto($oc->Iva),
                      "totalordencompra"=>Helpers::convertirvalorcorrecto($oc->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.ordenescompra.formato_pdf_ordenescompra', compact('data'))
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
                        ->attachData($pdf->output(), "OrdenCompraNo".$emaildocumento.".pdf");
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

    //exportar ordenes de compra en excel
    public function ordenes_compra_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new OrdenesDeCompraExport($this->campos_consulta,$request->periodo), "ordenesdecompra-".$request->periodo.".xlsx");   
    }

    //configurar tabla
    public function ordenes_compra_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'OrdenesDeCompra')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('ordenes_compra');
    }
    
}
