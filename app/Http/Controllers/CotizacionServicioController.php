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
use App\Exports\CotizacionesServiciosExport;
use App\CotizacionServicio;
use App\CotizacionServicioDetalle;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Serie;
use App\TipoOrdenCompra;
use App\TipoUnidad;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\Cliente;
use App\Agente;
use App\Servicio;
use App\Vine;
use App\Configuracion_Tabla;
use App\VistaCotizacionServicio;
use App\VistaObtenerExistenciaProducto;
use App\Existencia;
use Config;
use Mail;
use Schema;


class CotizacionServicioController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CotizacionesServicio')->first();
        //consultas ordenadas
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //campos vista
        $this->camposvista = [];
        foreach (explode(",", $this->configuracion_tabla->campos_activados) as $campo){
            array_push($this->camposvista, $campo);
        }
        foreach (explode(",", $this->configuracion_tabla->campos_desactivados) as $campo){
            array_push($this->camposvista, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function cotizaciones_servicios(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('cotizaciones_servicios_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cotizaciones_servicios_exportar_excel');
        $rutacreardocumento = route('cotizaciones_servicios_generar_pdfs');
        return view('registros.cotizacionesservicios.cotizacionesservicios', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener todos los registros
    public function cotizaciones_servicios_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCotizacionServicio::select($this->campos_consulta)->where('Periodo', $periodo);
            return DataTables::of($data)
                    ->order(function ($query) {
                        if($this->configuracion_tabla->primerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->primerordenamiento, '' . $this->configuracion_tabla->formaprimerordenamiento . '');
                        }
                        if($this->configuracion_tabla->segundoordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->segundoordenamiento, '' . $this->configuracion_tabla->formasegundoordenamiento . '');
                        }
                        if($this->configuracion_tabla->tercerordenamiento != 'omitir'){
                            $query->orderBy($this->configuracion_tabla->tercerordenamiento, '' . $this->configuracion_tabla->formatercerordenamiento . '');
                        }
                    })
                    ->addColumn('operaciones', function($data) use ($tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Cotizacion .'\')">Cambios</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Cotizacion .'\')">Bajas</a></li>'.
                                                '<li><a href="'.route('cotizaciones_servicios_generar_pdfs_indiv',$data->Cotizacion).'" target="_blank">Ver Documento PDF - Formato Interno</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Cotizacion .'\',1)">Enviar Documento por Correo - Formato Interno</a></li>'.
                                                '<li><a href="'.route('cotizaciones_servicios_generar_pdfs_cliente_indiv',$data->Cotizacion).'" target="_blank">Ver Documento PDF - Formato Cliente</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Cotizacion .'\',0)">Enviar Documento por Correo - Formato Cliente</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Kilometros', function($data){ return $data->Kilometros; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 
    //obtener series documento
    public function cotizaciones_servicios_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'CotizacionesServicios')->where('Usuario', Auth::user()->user)->get();
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
    public function cotizaciones_servicios_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionServicio',$request->Serie);
        return response()->json($folio);
    }
    //obtener el ultimo folio de la tabla
    public function cotizaciones_servicios_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionServicio',$request->serie);
        return response()->json($folio);
    }
    //obtener fecha date time actual
    public function cotizaciones_servicios_obtener_fecha_actual_datetimelocal(Request $request){
        $fechadatetimelocal = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechadatetimelocal);
    }
    //obtener tipos unidades
    public function cotizaciones_servicios_obtener_tipos_unidades(Request $request){
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $select_tipos_unidades = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tipo){
            $select_tipos_unidades = $select_tipos_unidades."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidades);
    }
    //obtener clientes
    public function cotizaciones_servicios_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = DB::table('Clientes as c')
            ->leftJoin('Agentes as a', 'a.Numero', '=', 'c.Agente')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'a.Numero AS NumeroAgente', 'a.Nombre AS NombreAgente')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "ASC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\',\''.Helpers::convertirvalorcorrecto($data->Credito).'\',\''.Helpers::convertirvalorcorrecto($data->Saldo).'\',\''.$data->NumeroAgente .'\',\''.$data->NombreAgente .'\',\''.$data->Plazo .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener agentes
    public function cotizaciones_servicios_obtener_agentes(Request $request){
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
    //obtener cliente por numero
    public function cotizaciones_servicios_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $credito = '';
        $saldo = '';
        $plazo  ='';
        $numeroagente = '';
        $nombreagente = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $agente = Agente::where('Numero', $cliente->Agente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
            $plazo = $cliente->Plazo;
            $numeroagente = $agente->Numero;
            $nombreagente = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'credito' => $credito,
            'saldo' => $saldo,
            'plazo' => $plazo,
            'numeroagente' => $numeroagente,
            'nombreagente' => $nombreagente
        );
        return response()->json($data);
    }
    //obtener agente por numero
    public function cotizaciones_servicios_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $existeagente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data); 
    }//obtener vines
    public function cotizaciones_servicios_obtener_vines(Request $request){
        if($request->ajax()){
            $data = Vine::where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarvin('.$data->Cliente.',\''.$data->Economico.'\',\''.$data->Vin.'\',\''.$data->Placas.'\',\''.$data->Motor.'\',\''.$data->Marca.'\',\''.$data->Modelo.'\',\''.$data->Año.'\',\''.$data->Color.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener vin por numero
    public function cotizaciones_servicios_obtener_vin_por_numero(Request $request){
        $cliente = '';
        $economico = '';
        $vin = '';
        $placas = '';
        $motor = '';
        $marca = '';
        $modelo = '';
        $año = '';
        $color = '';
        $existevin = Vine::where('Vin', $request->vin)->where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->count();
        if($existevin > 0){
            $v = DB::table('Vines')->where('Vin', $request->vin)->where('Cliente', $request->numeroclientefacturaa)->where('Status', 'ALTA')->first();
            $cliente = $v->Cliente;
            $economico = $v->Economico;
            $vin = $v->Vin;
            $placas = $v->Placas;
            $motor = $v->Motor;
            $marca = $v->Marca;
            $modelo = $v->Modelo;
            $año = $v->Año;
            $color = $v->Color;
        }
        $data = array(
            'cliente' => $cliente,
            'economico' => $economico,
            'vin' => $vin,
            'placas' => $placas,
            'motor' => $motor,
            'marca' => $marca,
            'modelo' => $modelo,
            'año' => $año,
            'color' => $color
        );
        return response()->json($data); 
    }
    //obtener productos
    public function cotizaciones_servicios_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    //obtener producto por codigo
    public function cotizaciones_servicios_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
                'Insumo' => $producto->Insumo,
                'ClaveProducto' => $producto->ClaveProducto,
                'ClaveUnidad' => $producto->ClaveUnidad,
                'CostoDeLista' => Helpers::convertirvalorcorrecto($producto->CostoDeLista),
                'contarproductos' => $contarproductos
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Producto' => '',
                'Unidad' => '',
                'Costo' => '',
                'Impuesto' => '',
                'SubTotal' => '',
                'Existencias' => '',
                'Insumo' => '',
                'ClaveProducto' => '',
                'ClaveUnidad' => '',
                'CostoDeLista' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);
    }
    //obtener existencias almacen uno
    public function cotizaciones_servicios_obtener_existencias_almacen_uno(Request $request){
        $contarexistenciacodigo = Existencia::where('Codigo', $request->Codigo)->where('Almacen', 1)->count();
        if($contarexistenciacodigo > 0){
            $existencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen', 1)->first();
            $existenciacodigo = Helpers::convertirvalorcorrecto($existencia->Existencias);
        }else{
            $existenciacodigo = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($existenciacodigo);
    }
    //obtener servicios
    public function cotizaciones_servicios_obtener_servicios(Request $request){
        $codigoservicioabuscar = $request->codigoservicioabuscar;
        $tipooperacion = $request->tipooperacion;
        $data = Servicio::where('Codigo', 'like', '%' . $codigoservicioabuscar . '%');
        return Datatables::of($data)
            ->addColumn('operaciones', function($data) use ($tipooperacion){
                $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaservicio(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Servicio, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.$data->Familia.'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
                return $boton;
            })
            ->addColumn('Venta', function($data){ 
                return Helpers::convertirvalorcorrecto($data->Venta);
            })
            ->addColumn('Cantidad', function($data){ 
                return Helpers::convertirvalorcorrecto($data->Cantidad);
            })
            ->rawColumns(['operaciones'])
            ->make(true);
    }
    //obtener servicio por codigo
    public function cotizaciones_servicios_obtener_servicio_por_codigo(Request $request){
        $codigoservicioabuscar = $request->codigoservicioabuscar;
        $contarservicios = Servicio::where('Codigo', $codigoservicioabuscar)->count();
        if($contarservicios > 0){
            $servicio = Servicio::where('Codigo', $codigoservicioabuscar)->first();
            $data = array(
                'Codigo' => $servicio->Codigo,
                'Servicio' => htmlspecialchars($servicio->Servicio, ENT_QUOTES),
                'Unidad' => $servicio->Unidad,
                'Familia' => $servicio->Familia,
                'Costo' => Helpers::convertirvalorcorrecto($servicio->Costo),
                'Venta' => Helpers::convertirvalorcorrecto($servicio->Venta),
                'Cantidad' => Helpers::convertirvalorcorrecto($servicio->Cantidad),
                'contarservicios' => $contarservicios
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Servicio' => '',
                'Unidad' => '',
                'Familia' => '',
                'Costo' => '',
                'Venta' => '',
                'Cantidad' => '',
                'contarservicios' => $contarservicios
            );
        }
        return response()->json($data);
    }
    //altas
    public function cotizaciones_servicios_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionServicio',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $cotizacion = $folio.'-'.$request->serie;
        $CotizacionServicio = new CotizacionServicio;
        $CotizacionServicio->Cotizacion=$cotizacion;
        $CotizacionServicio->Serie=$request->serie;
        $CotizacionServicio->Folio=$folio;
        $CotizacionServicio->Cliente=$request->numerocliente;
        $CotizacionServicio->Agente=$request->numeroagente;
        $CotizacionServicio->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CotizacionServicio->Plazo=$request->plazo;
        $CotizacionServicio->Unidad=$request->unidad;
        $CotizacionServicio->Referencia=$request->referencia;
        $CotizacionServicio->Importe=$request->importetotal;
        $CotizacionServicio->Descuento=$request->descuentototal;
        $CotizacionServicio->SubTotal=$request->subtotaltotal;
        $CotizacionServicio->Iva=$request->ivatotal;
        $CotizacionServicio->Total=$request->totaltotal;
        $CotizacionServicio->Costo=$request->costototal;
        $CotizacionServicio->Comision=$request->comisiontotal;
        $CotizacionServicio->Utilidad=$request->utilidadtotal;
        $CotizacionServicio->Vin=$request->vin;
        $CotizacionServicio->Motor=$request->motor;
        $CotizacionServicio->Marca=$request->marca;
        $CotizacionServicio->Modelo=$request->modelo;
        $CotizacionServicio->Año=$request->ano;
        $CotizacionServicio->Kilometros=$request->kilometros;
        $CotizacionServicio->Placas=$request->placas;
        $CotizacionServicio->Economico=$request->economico;
        $CotizacionServicio->Color=$request->color;
        $CotizacionServicio->TipoServicio=$request->tiposervicio;
        $CotizacionServicio->Obs=$request->observaciones;
        $CotizacionServicio->Status="POR CARGAR";
        $CotizacionServicio->Usuario=Auth::user()->user;
        $CotizacionServicio->Periodo=$this->periodohoy;
        $CotizacionServicio->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES SERVICIOS";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR CARGAR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        $item = 1;
        $itemservicio = 1;
        //refacciones
        if($request->numerofilas > 0){
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
                $CotizacionServicioDetalle=new CotizacionServicioDetalle;
                $CotizacionServicioDetalle->Cotizacion = $cotizacion;
                $CotizacionServicioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CotizacionServicioDetalle->Codigo = $codigoproductopartida;
                $CotizacionServicioDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $CotizacionServicioDetalle->Unidad = $request->unidadproductopartida [$key];
                $CotizacionServicioDetalle->Cantidad =  $request->cantidadpartida [$key];
                $CotizacionServicioDetalle->Precio =  $request->preciopartida [$key];
                $CotizacionServicioDetalle->PrecioNeto =  $request->totalpesospartida [$key];
                $CotizacionServicioDetalle->Importe =  $request->importepartida [$key];
                $CotizacionServicioDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $CotizacionServicioDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $CotizacionServicioDetalle->SubTotal =  $request->subtotalpartida [$key];
                $CotizacionServicioDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $CotizacionServicioDetalle->Iva =  $request->ivapesospartida [$key];
                $CotizacionServicioDetalle->Total =  $request->totalpesospartida [$key];
                $CotizacionServicioDetalle->Costo =  $request->costopartida [$key];
                $CotizacionServicioDetalle->CostoTotal =  $request->costototalpartida [$key];
                $CotizacionServicioDetalle->Com =  $request->comisionporcentajepartida [$key];
                $CotizacionServicioDetalle->Comision =  $request->comisionespesospartida [$key];
                $CotizacionServicioDetalle->Utilidad =  $request->utilidadpartida [$key];
                $CotizacionServicioDetalle->Moneda =  $request->monedapartida [$key];
                $CotizacionServicioDetalle->CostoDeLista =  $request->costolistapartida [$key];
                $CotizacionServicioDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
                $CotizacionServicioDetalle->Existencias =  $request->existenciaactualpartida [$key];
                $CotizacionServicioDetalle->Departamento =  "REFACCIONES";
                $CotizacionServicioDetalle->Item = $item;
                $CotizacionServicioDetalle->save();
                $item++;
            }
        }
        //servicios
        if($request->numerofilasservicios > 0){
            foreach ($request->codigoserviciopartida as $key => $codigoserviciopartida){             
                $CotizacionServicioDetalle=new CotizacionServicioDetalle;
                $CotizacionServicioDetalle->Cotizacion = $cotizacion;
                $CotizacionServicioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CotizacionServicioDetalle->Codigo = $codigoserviciopartida;
                $CotizacionServicioDetalle->Descripcion = $request->descripcionserviciopartida [$key];
                $CotizacionServicioDetalle->Unidad = $request->unidadserviciopartida [$key];
                $CotizacionServicioDetalle->Cantidad =  $request->cantidadpartidaservicio [$key];
                $CotizacionServicioDetalle->Precio =  $request->preciopartidaservicio [$key];
                $CotizacionServicioDetalle->PrecioNeto =  $request->totalpesospartidaservicio [$key];
                $CotizacionServicioDetalle->Importe =  $request->importepartidaservicio [$key];
                $CotizacionServicioDetalle->Dcto =  $request->descuentoporcentajepartidaservicio [$key];
                $CotizacionServicioDetalle->Descuento =  $request->descuentopesospartidaservicio  [$key];
                $CotizacionServicioDetalle->SubTotal =  $request->subtotalpartidaservicio [$key];
                $CotizacionServicioDetalle->Impuesto =  $request->ivaporcentajepartidaservicio [$key];
                $CotizacionServicioDetalle->Iva =  $request->ivapesospartidaservicio [$key];
                $CotizacionServicioDetalle->Total =  $request->totalpesospartidaservicio [$key];
                $CotizacionServicioDetalle->Costo =  $request->costopartidaservicio [$key];
                $CotizacionServicioDetalle->CostoTotal =  $request->costototalpartidaservicio [$key];
                $CotizacionServicioDetalle->Com =  $request->comisionporcentajepartidaservicio [$key];
                $CotizacionServicioDetalle->Comision =  $request->comisionespesospartidaservicio [$key];
                $CotizacionServicioDetalle->Utilidad =  $request->utilidadpartidaservicio [$key];
                $CotizacionServicioDetalle->CostoDeLista =  Helpers::convertirvalorcorrecto(0);
                $CotizacionServicioDetalle->TipoDeCambio =  Helpers::convertirvalorcorrecto(0);
                $CotizacionServicioDetalle->Existencias =  Helpers::convertirvalorcorrecto(0);
                $CotizacionServicioDetalle->Departamento =  "SERVICIO";
                $CotizacionServicioDetalle->Item = $itemservicio;
                $CotizacionServicioDetalle->save();
                $itemservicio++;
            }
        }
        return response()->json($CotizacionServicio);
    }

    //verificar baja
    public function cotizaciones_servicios_verificar_baja(Request $request){
        $CotizacionServicio = CotizacionServicio::where('Cotizacion', $request->cotizaciondesactivar)->first();
        $ContarCotizacionServicioDetalle = CotizacionServicioDetalle::where('Cotizacion', $request->cotizaciondesactivar)->count();
        $errores = '';
        if($ContarCotizacionServicioDetalle){
            $DetallesCotizacionUtilizada = OrdenTrabajoDetalle::where('Cotizacion', $request->cotizaciondesactivar)->get();
            foreach($DetallesCotizacionUtilizada as $detalle){
                $errores = $errores.'Error la cotización no se puede cancelar, porque existen registros de cotizaciones en la orden de trabajo No:'.$detalle->Orden.'<br>';
            }  
        }
        $resultadofechas = Helpers::compararanoymesfechas($CotizacionServicio->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $CotizacionServicio->Status
        );
        return response()->json($data);
    }

    //bajas
    public function cotizaciones_servicios_bajas(Request $request){
        $CotizacionServicio = CotizacionServicio::where('Cotizacion', $request->cotizaciondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CotizacionServicio::where('Cotizacion', $request->cotizaciondesactivar)
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
        $detalles = CotizacionServicioDetalle::where('Cotizacion', $request->cotizaciondesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            CotizacionServicioDetalle::where('Cotizacion', $request->cotizaciondesactivar)
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
        $BitacoraDocumento->Documento = "COTIZACIONES SERVICIOS";
        $BitacoraDocumento->Movimiento = $request->cotizaciondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CotizacionServicio->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CotizacionServicio);
    }

    //obtener registro
    public function cotizaciones_servicios_obtener_cotizacion_servicio(Request $request){
        $cotizacion = CotizacionServicio::where('Cotizacion', $request->cotizacionmodificar)->first();
        $cliente = Cliente::where('Numero', $cotizacion->Cliente)->first();
        $agente = Agente::where('Numero', $cotizacion->Agente)->first();
        //detalles refacciones
        $importe = 0;
        $descuento = 0;
        $subtotal = 0;
        $iva = 0;
        $total = 0;
        $costo = 0;
        $utilidad = 0;
        $comision = 0;
        $filasdetallescotizacion = '';
        $contadorproductos = 0;
        $contadorfilas = 0;
        $numerodetallescotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->cotizacionmodificar)->where('Departamento', 'REFACCIONES')->count();
        if($numerodetallescotizacion > 0){
            $detallescotizacion= CotizacionServicioDetalle::where('Cotizacion', $request->cotizacionmodificar)->where('Departamento', 'REFACCIONES')->orderBy('Item', 'ASC')->get();
            $tipo="modificacion";
            foreach($detallescotizacion as $dc){
                $producto = Producto::where('Codigo', $dc->Codigo)->first();
                $contarexistenciacodigo = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', 1)->count();
                if($contarexistenciacodigo > 0){
                    $existencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', 1)->first();
                    $existenciacodigo = Helpers::convertirvalorcorrecto($existencia->Existencias);
                }else{
                    $existenciacodigo = Helpers::convertirvalorcorrecto(0);
                }
                //indice de surtimiento
                if($dc->Cantidad == 0){
                    $indicesurtimientopartida = 0;
                }else{
                    $multiplicacionexistenciaporindicesurtimiento = $existenciacodigo * 100;
                    $indicesurtimientopartida = $multiplicacionexistenciaporindicesurtimiento / $dc->Cantidad;
                }
                $filasdetallescotizacion= $filasdetallescotizacion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dc->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$dc->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                            
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'.$existenciacodigo.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodxl cantidadsolicitadapartida" name="cantidadsolicitadapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm indicesurtimientopartida" name="indicesurtimientopartida[]" value="'.Helpers::convertirvalorcorrecto($indicesurtimientopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dc->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $importe = $importe + $dc->Importe;
                $descuento = $descuento + $dc->Descuento;
                $subtotal = $subtotal + $dc->SubTotal;
                $iva = $iva + $dc->Iva;
                $total = $total + $dc->Total;
                $costo = $costo + $dc->Costo;
                $utilidad = $utilidad + $dc->Utilidad;
                $comision = $comision + $dc->Comision;
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
        }   
        //detalles servicios
        $importeservicio = 0;
        $descuentoservicio = 0;
        $subtotalservicio = 0;
        $ivaservicio = 0;
        $totalservicio = 0;
        $costoservicio = 0;
        $utilidadservicio = 0;
        $comisionservicio = 0;
        $filasdetallesservicioscotizacion = '';
        $contadorservicios = 0;
        $contadorfilasservicios = 0;
        $numerodetallesservicioscotizacion = CotizacionServicioDetalle::where('Cotizacion', $request->cotizacionmodificar)->where('Departamento', 'SERVICIO')->count();
        if($numerodetallesservicioscotizacion > 0){
            $detallesservicioscotizacion= CotizacionServicioDetalle::where('Cotizacion', $request->cotizacionmodificar)->where('Departamento', 'SERVICIO')->orderBy('Item', 'ASC')->get();
            $tipo="modificacion";
            foreach($detallesservicioscotizacion as $dsc){
                $filasdetallesservicioscotizacion= $filasdetallesservicioscotizacion.
                '<tr class="filasservicios" id="filaservicio'.$contadorservicios.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfilaservicio('.$contadorservicios.')">X</div><input type="hidden" class="form-control itempartidaservicio" name="itempartidaservicio[]" value="'.$dsc->Item.'" readonly><input type="hidden" class="form-control agregadoenservicio" name="agregadoenservicio[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoserviciopartida" name="codigoserviciopartida[]" value="'.$dsc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dsc->Codigo.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodxl descripcionserviciopartida" name="descripcionserviciopartida[]" value="'.htmlspecialchars($dsc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadserviciopartida" name="unidadserviciopartida[]" value="'.$dsc->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$dsc->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidaservicio" name="cantidadpartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('.$contadorfilasservicios.');cambiodecantidadpartidaservicio('.$contadorfilasservicios.',\''.$tipo .'\');">'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaservicio" name="preciopartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('.$contadorfilasservicios.');cambiodepreciopartidaservicio('.$contadorfilasservicios.',\''.$tipo .'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartidaservicio" name="importepartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartidaservicio" name="descuentoporcentajepartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartidaservicio('.$contadorfilasservicios.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartidaservicio" name="descuentopesospartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartidaservicio('.$contadorfilasservicios.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartidaservicio" name="subtotalpartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartidaservicio" name="ivaporcentajepartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasservicios('.$contadorfilasservicios.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartidaservicio" name="ivapesospartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartidaservicio" name="totalpesospartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartidaservicio" name="costopartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartidaservicio" name="costototalpartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->CostoTotal).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartidaservicio" name="comisionporcentajepartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartidaservicio('.$contadorfilasservicios.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartidaservicio" name="comisionespesospartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartidaservicio" name="utilidadpartidaservicio[]" value="'.Helpers::convertirvalorcorrecto($dsc->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $importeservicio = $importeservicio + $dsc->Importe;
                $descuentoservicio = $descuentoservicio + $dsc->Descuento;
                $subtotalservicio = $subtotalservicio + $dsc->SubTotal;
                $ivaservicio = $ivaservicio + $dsc->Iva;
                $totalservicio = $totalservicio + $dsc->Total;
                $costoservicio = $costoservicio + $dsc->Costo;
                $utilidadservicio = $utilidadservicio + $dsc->Utilidad;
                $comisionservicio = $comisionservicio + $dsc->Comision;
                $contadorservicios++;
                $contadorfilasservicios++;
            }
        }else{
            $filasdetallesservicioscotizacion = '';
        }   
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($cotizacion->Status != 'POR CARGAR'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($cotizacion->Status != 'POR CARGAR'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($cotizacion->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }  
        $data = array(
            "cotizacion" => $cotizacion,
            "cliente" => $cliente,
            "agente" => $agente,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "filasdetallesservicioscotizacion" => $filasdetallesservicioscotizacion,
            "numerodetallesservicioscotizacion" => $numerodetallesservicioscotizacion,
            "contadorservicios" => $contadorservicios,
            "contadorfilasservicios" => $contadorfilasservicios,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "kilometros" => Helpers::convertirvalorcorrecto($cotizacion->Kilometros),
            "importe" => Helpers::convertirvalorcorrecto($importe),
            "descuento" => Helpers::convertirvalorcorrecto($descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($subtotal),
            "iva" => Helpers::convertirvalorcorrecto($iva),
            "total" => Helpers::convertirvalorcorrecto($total),
            "costo" => Helpers::convertirvalorcorrecto($costo),
            "utilidad" => Helpers::convertirvalorcorrecto($utilidad),
            "comision" => Helpers::convertirvalorcorrecto($comision),
            "importeservicio" => Helpers::convertirvalorcorrecto($importeservicio),
            "descuentoservicio" => Helpers::convertirvalorcorrecto($descuentoservicio),
            "subtotalservicio" => Helpers::convertirvalorcorrecto($subtotalservicio),
            "ivaservicio" => Helpers::convertirvalorcorrecto($ivaservicio),
            "totalservicio" => Helpers::convertirvalorcorrecto($totalservicio),
            "costoservicio" => Helpers::convertirvalorcorrecto($costoservicio),
            "utilidadservicio" => Helpers::convertirvalorcorrecto($utilidadservicio),
            "comisionservicio" => Helpers::convertirvalorcorrecto($comisionservicio),
            "importetotal" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuentototal" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotaltotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "ivatotal" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "totaltotal" => Helpers::convertirvalorcorrecto($cotizacion->Total),
            "costototal" => Helpers::convertirvalorcorrecto($cotizacion->Costo),
            "utilidadtotal" => Helpers::convertirvalorcorrecto($cotizacion->Utilidad),
            "comisiontotal" => Helpers::convertirvalorcorrecto($cotizacion->Comision),
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito),
            "saldo" => Helpers::convertirvalorcorrecto($cliente->Saldo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function cotizaciones_servicios_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $cotizacion = $request->folio.'-'.$request->serie;
        $CotizacionServicio = CotizacionServicio::where('Cotizacion', $cotizacion)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles del documento
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles
        //REFACCIONES
        //array partidas antes de modificacion
        $ArrayDetallesCotizacionAnterior = Array();
        $DetallesCotizacionAnterior = CotizacionServicioDetalle::where('Cotizacion', $cotizacion)->where('Departamento', 'REFACCIONES')->get();
        foreach($DetallesCotizacionAnterior as $detalle){
            array_push($ArrayDetallesCotizacionAnterior, $detalle->Cotizacion.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesCotizacionNuevo = Array();
        if($request->numerofilas > 0){
            foreach ($request->codigoproductopartida as $key => $nuevocodigo){
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesCotizacionNuevo, $cotizacion.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                } 
            }  
        }
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesCotizacionAnterior, $ArrayDetallesCotizacionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle del documento eliminado
                $eliminardetallecotizacion = CotizacionServicioDetalle::where('Cotizacion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->where('Departamento', 'REFACCIONES')->forceDelete();
            }
        }
        //SERVICIOS
        //array partidas antes de modificacion
        $ArrayDetallesCotizacionAnterior = Array();
        $DetallesCotizacionAnterior = CotizacionServicioDetalle::where('Cotizacion', $cotizacion)->where('Departamento', 'SERVICIO')->get();
        foreach($DetallesCotizacionAnterior as $detalle){
            array_push($ArrayDetallesCotizacionAnterior, $detalle->Cotizacion.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesCotizacionNuevo = Array();
        if($request->numerofilasservicios > 0){
            foreach ($request->codigoserviciopartida as $key => $nuevocodigo){
                if($request->agregadoenservicio [$key] == 'NA'){
                    array_push($ArrayDetallesCotizacionNuevo, $cotizacion.'#'.$nuevocodigo.'#'.$request->itempartidaservicio [$key]);
                } 
            }  
        }
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesCotizacionAnterior, $ArrayDetallesCotizacionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle del documento eliminado
                $eliminardetallecotizacion = CotizacionServicioDetalle::where('Cotizacion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->where('Departamento', 'SERVICIO')->forceDelete();
            }
        }
        //modificar documento
        CotizacionServicio::where('Cotizacion', $cotizacion)
        ->update([
            'Cliente' => $request->numerocliente,
            'Agente' => $request->numeroagente,
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo' => $request->plazo,
            'Unidad' => $request->unidad,
            'Referencia' => $request->referencia,
            'Importe' => $request->importetotal,
            'Descuento' => $request->descuentototal,
            'SubTotal' => $request->subtotaltotal,
            'Iva' => $request->ivatotal,
            'Total' => $request->totaltotal,
            'Costo' => $request->costototal,
            'Comision' => $request->comisiontotal,
            'Utilidad' => $request->utilidadtotal,
            'Vin' => $request->vin,
            'Motor' => $request->motor,
            'Marca' => $request->marca,
            'Modelo' => $request->modelo,
            'Año' => $request->ano,
            'Kilometros' => $request->kilometros,
            'Placas' => $request->placas,
            'Economico' => $request->economico,
            'Color' => $request->color,
            'TipoServicio' => $request->tiposervicio,
            'Obs' => $request->observaciones
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES SERVICIOS";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CotizacionServicio->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        //Refacciones
        if($request->numerofilas > 0){
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
                //if la partida se agrego en la modificacion se agrega en los detalles del documento
                if($request->agregadoen [$key] == 'modificacion'){
                    $contaritem = CotizacionServicioDetalle::select('Item')->where('Cotizacion', $cotizacion)->where('Departamento', 'REFACCIONES')->count();
                    if($contaritem > 0){
                        $item = CotizacionServicioDetalle::select('Item')->where('Cotizacion', $cotizacion)->where('Departamento', 'REFACCIONES')->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $CotizacionServicioDetalle=new CotizacionServicioDetalle;
                    $CotizacionServicioDetalle->Cotizacion = $cotizacion;
                    $CotizacionServicioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $CotizacionServicioDetalle->Codigo = $codigoproductopartida;
                    $CotizacionServicioDetalle->Descripcion = $request->descripcionproductopartida [$key];
                    $CotizacionServicioDetalle->Unidad = $request->unidadproductopartida [$key];
                    $CotizacionServicioDetalle->Cantidad =  $request->cantidadpartida [$key];
                    $CotizacionServicioDetalle->Precio =  $request->preciopartida [$key];
                    $CotizacionServicioDetalle->PrecioNeto =  $request->totalpesospartida [$key];
                    $CotizacionServicioDetalle->Importe =  $request->importepartida [$key];
                    $CotizacionServicioDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                    $CotizacionServicioDetalle->Descuento =  $request->descuentopesospartida  [$key];
                    $CotizacionServicioDetalle->SubTotal =  $request->subtotalpartida [$key];
                    $CotizacionServicioDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                    $CotizacionServicioDetalle->Iva =  $request->ivapesospartida [$key];
                    $CotizacionServicioDetalle->Total =  $request->totalpesospartida [$key];
                    $CotizacionServicioDetalle->Costo =  $request->costopartida [$key];
                    $CotizacionServicioDetalle->CostoTotal =  $request->costototalpartida [$key];
                    $CotizacionServicioDetalle->Com =  $request->comisionporcentajepartida [$key];
                    $CotizacionServicioDetalle->Comision =  $request->comisionespesospartida [$key];
                    $CotizacionServicioDetalle->Utilidad =  $request->utilidadpartida [$key];
                    $CotizacionServicioDetalle->Moneda =  $request->monedapartida [$key];
                    $CotizacionServicioDetalle->CostoDeLista =  $request->costolistapartida [$key];
                    $CotizacionServicioDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
                    $CotizacionServicioDetalle->Existencias =  $request->existenciaactualpartida [$key];
                    $CotizacionServicioDetalle->Departamento =  "REFACCIONES";
                    $CotizacionServicioDetalle->Item = $ultimoitem;
                    $CotizacionServicioDetalle->save();
                    $ultimoitem++;
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    CotizacionServicioDetalle::where('Cotizacion', $cotizacion)
                    ->where('Departamento', 'REFACCIONES')
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Codigo' => $codigoproductopartida,
                        'Descripcion' => $request->descripcionproductopartida [$key],
                        'Unidad' => $request->unidadproductopartida [$key],
                        'Cantidad' =>  $request->cantidadpartida [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'PrecioNeto' =>  $request->totalpesospartida [$key],
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
                        'CostoDeLista' =>  $request->costolistapartida [$key],
                        'TipoDeCambio' =>  $request->tipocambiopartida [$key],
                        'Existencias' =>  $request->existenciaactualpartida [$key]
                    ]);
                }
            }
        }
        //servicios
        if($request->numerofilasservicios > 0){
            foreach ($request->codigoserviciopartida as $key => $codigoserviciopartida){   
                //if la partida se agrego en la modificacion se agrega en los detalles del documento
                if($request->agregadoenservicio [$key] == 'modificacion'){  
                    $contaritem = CotizacionServicioDetalle::select('Item')->where('Cotizacion', $cotizacion)->where('Departamento', 'SERVICIO')->count();
                    if($contaritem > 0){
                        $item = CotizacionServicioDetalle::select('Item')->where('Cotizacion', $cotizacion)->where('Departamento', 'SERVICIO')->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitemservicio = $item[0]->Item+1;  
                    }else{
                        $ultimoitemservicio = 1;
                    }
                    $CotizacionServicioDetalle=new CotizacionServicioDetalle;
                    $CotizacionServicioDetalle->Cotizacion = $cotizacion;
                    $CotizacionServicioDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $CotizacionServicioDetalle->Codigo = $codigoserviciopartida;
                    $CotizacionServicioDetalle->Descripcion = $request->descripcionserviciopartida [$key];
                    $CotizacionServicioDetalle->Unidad = $request->unidadserviciopartida [$key];
                    $CotizacionServicioDetalle->Cantidad =  $request->cantidadpartidaservicio [$key];
                    $CotizacionServicioDetalle->Precio =  $request->preciopartidaservicio [$key];
                    $CotizacionServicioDetalle->PrecioNeto =  $request->totalpesospartidaservicio [$key];
                    $CotizacionServicioDetalle->Importe =  $request->importepartidaservicio [$key];
                    $CotizacionServicioDetalle->Dcto =  $request->descuentoporcentajepartidaservicio [$key];
                    $CotizacionServicioDetalle->Descuento =  $request->descuentopesospartidaservicio  [$key];
                    $CotizacionServicioDetalle->SubTotal =  $request->subtotalpartidaservicio [$key];
                    $CotizacionServicioDetalle->Impuesto =  $request->ivaporcentajepartidaservicio [$key];
                    $CotizacionServicioDetalle->Iva =  $request->ivapesospartidaservicio [$key];
                    $CotizacionServicioDetalle->Total =  $request->totalpesospartidaservicio [$key];
                    $CotizacionServicioDetalle->Costo =  $request->costopartidaservicio [$key];
                    $CotizacionServicioDetalle->CostoTotal =  $request->costototalpartidaservicio [$key];
                    $CotizacionServicioDetalle->Com =  $request->comisionporcentajepartidaservicio [$key];
                    $CotizacionServicioDetalle->Comision =  $request->comisionespesospartidaservicio [$key];
                    $CotizacionServicioDetalle->Utilidad =  $request->utilidadpartidaservicio [$key];
                    $CotizacionServicioDetalle->CostoDeLista =  Helpers::convertirvalorcorrecto(0);
                    $CotizacionServicioDetalle->TipoDeCambio =  Helpers::convertirvalorcorrecto(0);
                    $CotizacionServicioDetalle->Existencias =  Helpers::convertirvalorcorrecto(0);
                    $CotizacionServicioDetalle->Departamento =  "SERVICIO";
                    $CotizacionServicioDetalle->Item = $ultimoitemservicio;
                    $CotizacionServicioDetalle->save();
                    $ultimoitemservicio++;
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
                    CotizacionServicioDetalle::where('Cotizacion', $cotizacion)
                    ->where('Departamento', 'SERVICIO')
                    ->where('Item', $request->itempartidaservicio [$key])
                    ->update([
                        'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Codigo' => $codigoserviciopartida,
                        'Descripcion' => $request->descripcionserviciopartida [$key],
                        'Unidad' => $request->unidadserviciopartida [$key],
                        'Cantidad' =>  $request->cantidadpartidaservicio [$key],
                        'Precio' =>  $request->preciopartidaservicio [$key],
                        'PrecioNeto' =>  $request->totalpesospartidaservicio [$key],
                        'Importe' =>  $request->importepartidaservicio [$key],
                        'Dcto' =>  $request->descuentoporcentajepartidaservicio [$key],
                        'Descuento' =>  $request->descuentopesospartidaservicio  [$key],
                        'SubTotal' =>  $request->subtotalpartidaservicio [$key],
                        'Impuesto' =>  $request->ivaporcentajepartidaservicio [$key],
                        'Iva' =>  $request->ivapesospartidaservicio [$key],
                        'Total' =>  $request->totalpesospartidaservicio [$key],
                        'Costo' =>  $request->costopartidaservicio [$key],
                        'CostoTotal' =>  $request->costototalpartidaservicio [$key],
                        'Com' =>  $request->comisionporcentajepartidaservicio [$key],
                        'Comision' =>  $request->comisionespesospartidaservicio [$key],
                        'Utilidad' =>  $request->utilidadpartidaservicio [$key]
                    ]);
                }
            }
        }
        return response()->json($CotizacionServicio);
    }

    //buscar folio on key up
    public function cotizaciones_servicios_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = CotizacionServicio::where('Cotizacion', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Cotizacion .'\')"><i class="material-icons">done</i></div> ';
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
    public function cotizaciones_servicios_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $cotizacionesservicios = CotizacionServicio::whereIn('Cotizacion', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cotizacionesservicios = CotizacionServicio::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesservicios as $cs){
            $cotizacionesserviciosdetalle = CotizacionServicioDetalle::where('Cotizacion', $cs->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesserviciosdetalle as $csd){
                if($csd->Departamento == 'REFACCIONES'){
                    $producto = Producto::where('Codigo', $csd->Codigo)->first();
                    $ubicacion = $producto->Ubicacion;
                    $marca = Marca::where('Numero', $producto->Marca)->first();
                    $nombremarca = $marca->Nombre;
                }else{
                    $nombremarca = "";
                    $ubicacion = "";
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($csd->Cantidad),
                    "codigodetalle"=>$csd->Codigo,
                    "descripciondetalle"=>$csd->Descripcion,
                    "marcadetalle"=>$nombremarca,
                    "ubicaciondetalle"=>$ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($csd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($csd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($csd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cs->Cliente)->first();
            $data[]=array(
                      "cotizacionservicio"=>$cs,
                      "descuentocotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Descuento),
                      "subtotalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->SubTotal),
                      "ivacotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Iva),
                      "totalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesservicios.formato_pdf_cotizacionesservicios', compact('data'))
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
    public function cotizaciones_servicios_generar_pdfs_indiv($documento){
        $cotizacionesservicios = CotizacionServicio::where('Cotizacion', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesservicios as $cs){
            $cotizacionesserviciosdetalle = CotizacionServicioDetalle::where('Cotizacion', $cs->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesserviciosdetalle as $csd){
                if($csd->Departamento == 'REFACCIONES'){
                    $producto = Producto::where('Codigo', $csd->Codigo)->first();
                    $ubicacion = $producto->Ubicacion;
                    $marca = Marca::where('Numero', $producto->Marca)->first();
                    $nombremarca = $marca->Nombre;
                }else{
                    $nombremarca = "";
                    $ubicacion = "";
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($csd->Cantidad),
                    "codigodetalle"=>$csd->Codigo,
                    "descripciondetalle"=>$csd->Descripcion,
                    "marcadetalle"=>$nombremarca,
                    "ubicaciondetalle"=>$ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($csd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($csd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($csd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cs->Cliente)->first();
            $data[]=array(
                      "cotizacionservicio"=>$cs,
                      "descuentocotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Descuento),
                      "subtotalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->SubTotal),
                      "ivacotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Iva),
                      "totalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesservicios.formato_pdf_cotizacionesservicios', compact('data'))
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
    public function cotizaciones_servicios_obtener_datos_envio_email(Request $request){
        $cotizacionservicio = CotizacionServicio::where('Cotizacion', $request->documento)->first();
        $cliente = Cliente::where('Numero',$cotizacionservicio->Cliente)->first();
        $data = array(
            'cotizacionservicio' => $cotizacionservicio,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function cotizaciones_servicios_enviar_pdfs_email(Request $request){
        $cotizacionesservicios = CotizacionServicio::where('Cotizacion', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesservicios as $cs){
            $cotizacionesserviciosdetalle = CotizacionServicioDetalle::where('Cotizacion', $cs->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesserviciosdetalle as $csd){
                if($csd->Departamento == 'REFACCIONES'){
                    $producto = Producto::where('Codigo', $csd->Codigo)->first();
                    $ubicacion = $producto->Ubicacion;
                    $marca = Marca::where('Numero', $producto->Marca)->first();
                    $nombremarca = $marca->Nombre;
                }else{
                    $nombremarca = "";
                    $ubicacion = "";
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($csd->Cantidad),
                    "codigodetalle"=>$csd->Codigo,
                    "descripciondetalle"=>$csd->Descripcion,
                    "marcadetalle"=>$nombremarca,
                    "ubicaciondetalle"=>$ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($csd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($csd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($csd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cs->Cliente)->first();
            $data[]=array(
                      "cotizacionservicio"=>$cs,
                      "descuentocotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Descuento),
                      "subtotalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->SubTotal),
                      "ivacotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Iva),
                      "totalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesservicios.formato_pdf_cotizacionesservicios', compact('data'))
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
                        ->attachData($pdf->output(), "CotizacionServicioNo".$emaildocumento.".pdf");
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


    //generacion de formato en PDF
    public function cotizaciones_servicios_generar_pdfs_cliente_indiv($documento){
        $cotizacionesservicios = CotizacionServicio::where('Cotizacion', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesservicios as $cs){
            $cotizacionesserviciosdetalle = CotizacionServicioDetalle::where('Cotizacion', $cs->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesserviciosdetalle as $csd){
                if($csd->Departamento == 'REFACCIONES'){
                    $producto = Producto::where('Codigo', $csd->Codigo)->first();
                    $ubicacion = $producto->Ubicacion;
                    $marca = Marca::where('Numero', $producto->Marca)->first();
                    $nombremarca = $marca->Nombre;
                }else{
                    $nombremarca = "";
                    $ubicacion = "";
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($csd->Cantidad),
                    "codigodetalle"=>$csd->Codigo,
                    "descripciondetalle"=>$csd->Descripcion,
                    "marcadetalle"=>$nombremarca,
                    "ubicaciondetalle"=>$ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($csd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($csd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($csd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cs->Cliente)->first();
            $data[]=array(
                      "cotizacionservicio"=>$cs,
                      "descuentocotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Descuento),
                      "subtotalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->SubTotal),
                      "ivacotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Iva),
                      "totalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesservicios.formato_pdf_cliente_cotizacionesservicios', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //enviar pdf por emial
    public function cotizaciones_servicios_enviar_pdfs_cliente_email(Request $request){
        $cotizacionesservicios = CotizacionServicio::where('Cotizacion', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesservicios as $cs){
            $cotizacionesserviciosdetalle = CotizacionServicioDetalle::where('Cotizacion', $cs->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesserviciosdetalle as $csd){
                if($csd->Departamento == 'REFACCIONES'){
                    $producto = Producto::where('Codigo', $csd->Codigo)->first();
                    $ubicacion = $producto->Ubicacion;
                    $marca = Marca::where('Numero', $producto->Marca)->first();
                    $nombremarca = $marca->Nombre;
                }else{
                    $nombremarca = "";
                    $ubicacion = "";
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($csd->Cantidad),
                    "codigodetalle"=>$csd->Codigo,
                    "descripciondetalle"=>$csd->Descripcion,
                    "marcadetalle"=>$nombremarca,
                    "ubicaciondetalle"=>$ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($csd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($csd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($csd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cs->Cliente)->first();
            $data[]=array(
                      "cotizacionservicio"=>$cs,
                      "descuentocotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Descuento),
                      "subtotalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->SubTotal),
                      "ivacotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Iva),
                      "totalcotizacionservicio"=>Helpers::convertirvalorcorrecto($cs->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesservicios.formato_pdf_cliente_cotizacionesservicios', compact('data'))
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
                        ->attachData($pdf->output(), "CotizacionServicioClienteNo".$emaildocumento.".pdf");
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
    public function cotizaciones_servicios_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new CotizacionesServiciosExport($this->campos_consulta,$request->periodo), "cotizacionesservicios-".$request->periodo.".xlsx");   
    }
    //configurar tabla
    public function cotizaciones_servicios_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'CotizacionesServicio')
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
        return redirect()->route('cotizaciones_servicios');
    }

}
