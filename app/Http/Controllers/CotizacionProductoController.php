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
use App\Exports\PlantillasCotizacionesProductosExport;
use App\Imports\CotizacionesProductosImport;
use App\Exports\CotizacionesProductosExport;
use App\CotizacionProducto;
use App\CotizacionProductoDetalle;
use App\Remision;
use App\RemisionDetalle;
use App\Serie;
use App\TipoOrdenCompra;
use App\TipoCliente;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\Cliente;
use App\Agente;
use App\Configuracion_Tabla;
use App\VistaCotizacionProducto;
use App\VistaObtenerExistenciaProducto;
use App\Existencia;
use Config;
use Mail;
use Schema;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class CotizacionProductoController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CotizacionesProductos')->first();
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

    public function cotizaciones_productos(){
        $serieusuario = 'A';
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('cotizaciones_productos_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cotizaciones_productos_exportar_excel');
        $rutacreardocumento = route('cotizaciones_productos_generar_pdfs');
        $urlgenerarplantilla = route('cotizaciones_productos_generar_plantilla');
        return view('registros.cotizacionesproductos.cotizacionesproductos', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','urlgenerarplantilla'));
    }
    //obtener todos los registros
    public function cotizaciones_productos_obtener(Request $request){
        if($request->ajax()){
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCotizacionProducto::select($this->campos_consulta)->where('Periodo', $periodo);
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
                                                '<li><a href="'.route('cotizaciones_productos_generar_pdfs_indiv',$data->Cotizacion).'" target="_blank">Ver Documento PDF - Formato Interno</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Cotizacion .'\',1)">Enviar Documento por Correo - Formato Interno</a></li>'.
                                                '<li><a href="'.route('cotizaciones_productos_generar_pdfs_cliente_indiv',$data->Cotizacion).'" target="_blank">Ver Documento PDF - Formato Cliente</a></li>'.
                                                '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Cotizacion .'\',0)">Enviar Documento por Correo - Formato Cliente</a></li>'.
                                            '</ul>'.
                                        '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    } 
    //descargar plantilla
    public function cotizaciones_productos_generar_plantilla(){
        return Excel::download(new PlantillasCotizacionesProductosExport(), "plantillacotizacionproductos.xlsx"); 
    }
    //cargar partidas excel
    public function cotizaciones_productos_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new CotizacionesProductosImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallescotizacion = '';
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $numeroalmacen = $request->numeroalmacen;
        $tipooperacion = 'alta';
        $arraycodigosyaagregados = $porciones = explode(",", $request->arraycodigospartidas);
        foreach($partidasexcel as $partida){
            if($rowexcel > 0){
                if (in_array(strtoupper($partida[0]), $arraycodigosyaagregados)) {
                    
                }else{
                    $codigoabuscar = $partida[0];
                    $cantidadpartida = $partida[1];
                    $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->count();
                    if($contarproductos > 0){
                        $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->first();
                        $contarexistencia = Existencia::where('Codigo', ''.$codigoabuscar.'')->where('Almacen', $numeroalmacen)->count();
                        if($contarexistencia > 0){
                            $Existencia = Existencia::where('Codigo', ''.$codigoabuscar.'')->where('Almacen', $numeroalmacen)->first();
                            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                        }else{
                            $Existencias = Helpers::convertirvalorcorrecto(0);
                        }
                        if(Helpers::convertirvalorcorrecto($cantidadpartida) == 0){
                            $cantidad = 1;
                        }else{
                            $cantidad = $cantidadpartida;
                        }
                        //indice surtimiento partida
                        $multiplicacionexistenciaporindicesurtimiento = $Existencias * 100;
                        $indicesurtimientopartida = $multiplicacionexistenciaporindicesurtimiento / $cantidad;
                        //precio de la partida
                        $preciopartida = $producto->SubTotal;
                        //importe de la partida
                        $importepartida = $cantidad*$preciopartida;
                        //subtotal de la partida
                        $subtotalpartida =  $importepartida-0;
                        //iva en pesos de la partida
                        $multiplicacionivapesospartida = $subtotalpartida*$producto->Impuesto;
                        $ivapesospartida = $multiplicacionivapesospartida/100;
                        //total en pesos de la partida
                        $totalpesospartida = $subtotalpartida+$ivapesospartida;
                        //costo total
                        $costototalpartida  = $producto->Costo*$cantidad;
                        //comision de la partida
                        $comisionporcentajepartida = $subtotalpartida*0;
                        $comisionespesospartida = $comisionporcentajepartida/100;
                        //utilidad de la partida
                        $utilidadpartida = $subtotalpartida-$costototalpartida-$comisionespesospartida;
                        $tipo = "alta";
                        $filasdetallescotizacion= $filasdetallescotizacion.
                        '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]">'.$producto->Codigo.'</td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($producto->Producto, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$producto->Unidad.'</td>'.
                            '<td class="tdmod">'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'"  data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo .'\');">'.
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                           
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo .'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm existenciaactualpartida" name="existenciaactualpartida[]" value="'.$Existencias.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodxl cantidadsolicitadapartida" name="cantidadsolicitadapartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm indicesurtimientopartida" name="indicesurtimientopartida[]" value="'.Helpers::convertirvalorcorrecto($indicesurtimientopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '</tr>';
                        array_push($arraycodigosyaagregados, $producto->Codigo);
                        $contadorproductos++;
                        $contadorfilas++;
                    }
                }
            }
            $rowexcel++;
        }
        $data = array(
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data); 
    }
    //obtener series documento
    public function cotizaciones_productos_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'CotizacionesProductos')->where('Usuario', Auth::user()->user)->get();
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
    public function cotizaciones_productos_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionProducto',$request->Serie);
        return response()->json($folio);
    }
    //obtener el ultimo folio de la tabla
    public function cotizaciones_productos_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionProducto',$request->serie);
        return response()->json($folio);
    }
    //obtener fecha date time actual
    public function cotizaciones_productos_obtener_fecha_actual_datetimelocal(Request $request){
        $fechadatetimelocal = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fechadatetimelocal);
    }
    //obtener tipos ordenes de compra
    public function cotizaciones_productos_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();
        $select_tipos_ordenes_compra = "<option disabled hidden>Selecciona...</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener clientes
    public function cotizaciones_productos_obtener_clientes(Request $request){
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
    public function cotizaciones_productos_obtener_agentes(Request $request){
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
    public function cotizaciones_productos_obtener_cliente_por_numero(Request $request){
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
    public function cotizaciones_productos_obtener_agente_por_numero(Request $request){
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
    }
    //obtener productos
    public function cotizaciones_productos_obtener_productos(Request $request){
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
    public function cotizaciones_productos_obtener_producto_por_codigo(Request $request){
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
    public function cotizaciones_productos_obtener_existencias_almacen_uno(Request $request){
        $contarexistenciacodigo = Existencia::where('Codigo', $request->Codigo)->where('Almacen', 1)->count();
        if($contarexistenciacodigo > 0){
            $existencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen', 1)->first();
            $existenciacodigo = Helpers::convertirvalorcorrecto($existencia->Existencias);
        }else{
            $existenciacodigo = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($existenciacodigo);
    }
    //obtener saldo cliente
    public function cotizaciones_productos_obtener_nuevo_saldo_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        ///$nuevosaldo = $cliente->Saldo + $request->total;
        return response()->json(Helpers::convertirvalorcorrecto($cliente->Saldo));
    }
    //altas
    public function cotizaciones_productos_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\CotizacionProducto',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $cotizacion = $folio.'-'.$request->serie;
        $CotizacionProducto = new CotizacionProducto;
        $CotizacionProducto->Cotizacion=$cotizacion;
        $CotizacionProducto->Serie=$request->serie;
        $CotizacionProducto->Folio=$folio;
        $CotizacionProducto->Referencia=$request->referencia;
        $CotizacionProducto->Tipo=$request->tipo;
        $CotizacionProducto->Cliente=$request->numerocliente;
        $CotizacionProducto->Agente=$request->numeroagente;
        $CotizacionProducto->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CotizacionProducto->Plazo=$request->plazo;
        $CotizacionProducto->Importe=$request->importe;
        $CotizacionProducto->Descuento=$request->descuento;
        $CotizacionProducto->SubTotal=$request->subtotal;
        $CotizacionProducto->Iva=$request->iva;
        $CotizacionProducto->Total=$request->total;
        $CotizacionProducto->Costo=$request->costo;
        $CotizacionProducto->Comision=$request->comision;
        $CotizacionProducto->Utilidad=$request->utilidad;
        $CotizacionProducto->Obs=$request->observaciones;
        $CotizacionProducto->Status="POR CARGAR";
        $CotizacionProducto->Usuario=Auth::user()->user;
        $CotizacionProducto->Periodo=$this->periodohoy;
        $CotizacionProducto->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COTIZACIONES PRODUCTOS";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR CARGAR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $CotizacionProductoDetalle=new CotizacionProductoDetalle;
            $CotizacionProductoDetalle->Cotizacion = $cotizacion;
            $CotizacionProductoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $CotizacionProductoDetalle->Codigo = $codigoproductopartida;
            $CotizacionProductoDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $CotizacionProductoDetalle->Unidad = $request->unidadproductopartida [$key];
            $CotizacionProductoDetalle->Cantidad =  $request->cantidadpartida [$key];
            $CotizacionProductoDetalle->Precio =  $request->preciopartida [$key];
            $CotizacionProductoDetalle->PrecioNeto =  $request->totalpesospartida [$key];
            $CotizacionProductoDetalle->Importe =  $request->importepartida [$key];
            $CotizacionProductoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $CotizacionProductoDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $CotizacionProductoDetalle->SubTotal =  $request->subtotalpartida [$key];
            $CotizacionProductoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $CotizacionProductoDetalle->Iva =  $request->ivapesospartida [$key];
            $CotizacionProductoDetalle->Total =  $request->totalpesospartida [$key];
            $CotizacionProductoDetalle->Costo =  $request->costopartida [$key];
            $CotizacionProductoDetalle->CostoTotal =  $request->costototalpartida [$key];
            $CotizacionProductoDetalle->Com =  $request->comisionporcentajepartida [$key];
            $CotizacionProductoDetalle->Comision =  $request->comisionespesospartida [$key];
            $CotizacionProductoDetalle->Utilidad =  $request->utilidadpartida [$key];
            $CotizacionProductoDetalle->Moneda =  $request->monedapartida [$key];
            $CotizacionProductoDetalle->CostoDeLista =  $request->costolistapartida [$key];
            $CotizacionProductoDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
            $CotizacionProductoDetalle->Existencias =  $request->existenciaactualpartida [$key];
            $CotizacionProductoDetalle->InteresMeses =  $request->mesespartida [$key];
            $CotizacionProductoDetalle->InteresTasa =  $request->tasainterespartida  [$key];
            $CotizacionProductoDetalle->InteresMonto =  $request->montointerespartida  [$key];
            $CotizacionProductoDetalle->Item = $item;
            $CotizacionProductoDetalle->save();
            $item++;
        }
        return response()->json($CotizacionProducto);
    }

    //verificar baja
    public function cotizaciones_productos_verificar_baja(Request $request){
        $CotizacionProducto = CotizacionProducto::where('Cotizacion', $request->cotizaciondesactivar)->first();
        $ContarCotizacionProductoDetalle = CotizacionProductoDetalle::where('Cotizacion', $request->cotizaciondesactivar)->count();
        $errores = '';
        if($ContarCotizacionProductoDetalle){
            $DetallesCotizacionRemisionada = RemisionDetalle::where('Cotizacion', $request->cotizaciondesactivar)->get();
            foreach($DetallesCotizacionRemisionada as $detalle){
                $errores = $errores.'Error la cotización no se puede cancelar, porque existen registros de cotizaciones en la remision No:'.$detalle->Remision.'<br>';
            }  
        }
        $resultadofechas = Helpers::compararanoymesfechas($CotizacionProducto->Fecha);
        $data = array(
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $CotizacionProducto->Status
        );
        return response()->json($data);
    }

    //bajas
    public function cotizaciones_productos_bajas(Request $request){
        $CotizacionProducto = CotizacionProducto::where('Cotizacion', $request->cotizaciondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CotizacionProducto::where('Cotizacion', $request->cotizaciondesactivar)
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
        $detalles = CotizacionProductoDetalle::where('Cotizacion', $request->cotizaciondesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            CotizacionProductoDetalle::where('Cotizacion', $request->cotizaciondesactivar)
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
        $BitacoraDocumento->Documento = "COTIZACIONES PRODUCTOS";
        $BitacoraDocumento->Movimiento = $request->cotizaciondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CotizacionProducto->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CotizacionProducto);
    }

    //obtener registro
    public function cotizaciones_productos_obtener_cotizacion_producto(Request $request){
        $cotizacion = CotizacionProducto::where('Cotizacion', $request->cotizacionmodificar)->first();
        $cliente = Cliente::where('Numero', $cotizacion->Cliente)->first();
        $agente = Agente::where('Numero', $cotizacion->Agente)->first();
        //detalles
        $numerodetallescotizacion = CotizacionProductoDetalle::where('Cotizacion', $request->cotizacionmodificar)->count();
        if($numerodetallescotizacion > 0){
            $detallescotizacion= CotizacionProductoDetalle::where('Cotizacion', $request->cotizacionmodificar)->orderBy('Item', 'ASC')->get();
            $filasdetallescotizacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
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
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$dc->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                            
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
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
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMeses).'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresTasa).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMonto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallescotizacion = '';
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
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($cotizacion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->Total),
            "costo" => Helpers::convertirvalorcorrecto($cotizacion->Costo),
            "utilidad" => Helpers::convertirvalorcorrecto($cotizacion->Utilidad),
            "comision" => Helpers::convertirvalorcorrecto($cotizacion->Comision),
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito),
            "saldo" => Helpers::convertirvalorcorrecto($cliente->Saldo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function cotizaciones_productos_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $cotizacion = $request->folio.'-'.$request->serie;
        $CotizacionProducto = CotizacionProducto::where('Cotizacion', $cotizacion)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles del documento
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles
        //array partidas antes de modificacion
        $ArrayDetallesCotizacionAnterior = Array();
        $DetallesCotizacionAnterior = CotizacionProductoDetalle::where('Cotizacion', $cotizacion)->get();
        foreach($DetallesCotizacionAnterior as $detalle){
            array_push($ArrayDetallesCotizacionAnterior, $detalle->Cotizacion.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesCotizacionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesCotizacionNuevo, $cotizacion.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesCotizacionAnterior, $ArrayDetallesCotizacionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle del documento eliminado
                $eliminardetallecotizacion = CotizacionProductoDetalle::where('Cotizacion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar documento
        CotizacionProducto::where('Cotizacion', $cotizacion)
        ->update([
            'Cliente' => $request->numerocliente,
            'Agente' => $request->numeroagente,
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo' => $request->plazo,
            'Tipo' => $request->tipo,
            'Referencia' => $request->referencia,
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
        $BitacoraDocumento->Documento = "COTIZACIONES PRODUCTOS";
        $BitacoraDocumento->Movimiento = $cotizacion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CotizacionProducto->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles del documento
            if($request->agregadoen [$key] == 'modificacion'){      
                $contaritems = CotizacionProductoDetalle::select('Item')->where('Cotizacion', $cotizacion)->count();
                if($contaritems > 0){
                    $item = CotizacionProductoDetalle::select('Item')->where('Cotizacion', $cotizacion)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
                }else{
                    $ultimoitem = 1;
                }
                $CotizacionProductoDetalle=new CotizacionProductoDetalle;
                $CotizacionProductoDetalle->Cotizacion = $cotizacion;
                $CotizacionProductoDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CotizacionProductoDetalle->Codigo = $codigoproductopartida;
                $CotizacionProductoDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $CotizacionProductoDetalle->Unidad = $request->unidadproductopartida [$key];
                $CotizacionProductoDetalle->Cantidad =  $request->cantidadpartida [$key];
                $CotizacionProductoDetalle->Precio =  $request->preciopartida [$key];
                $CotizacionProductoDetalle->PrecioNeto =  $request->totalpesospartida [$key];
                $CotizacionProductoDetalle->Importe =  $request->importepartida [$key];
                $CotizacionProductoDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $CotizacionProductoDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $CotizacionProductoDetalle->SubTotal =  $request->subtotalpartida [$key];
                $CotizacionProductoDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $CotizacionProductoDetalle->Iva =  $request->ivapesospartida [$key];
                $CotizacionProductoDetalle->Total =  $request->totalpesospartida [$key];
                $CotizacionProductoDetalle->Costo =  $request->costopartida [$key];
                $CotizacionProductoDetalle->CostoTotal =  $request->costototalpartida [$key];
                $CotizacionProductoDetalle->Com =  $request->comisionporcentajepartida [$key];
                $CotizacionProductoDetalle->Comision =  $request->comisionespesospartida [$key];
                $CotizacionProductoDetalle->Utilidad =  $request->utilidadpartida [$key];
                $CotizacionProductoDetalle->Moneda =  $request->monedapartida [$key];
                $CotizacionProductoDetalle->CostoDeLista =  $request->costolistapartida [$key];
                $CotizacionProductoDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
                $CotizacionProductoDetalle->Existencias =  $request->existenciaactualpartida [$key];
                $CotizacionProductoDetalle->InteresMeses =  $request->mesespartida [$key];
                $CotizacionProductoDetalle->InteresTasa =  $request->tasainterespartida  [$key];
                $CotizacionProductoDetalle->InteresMonto =  $request->montointerespartida  [$key];
                $CotizacionProductoDetalle->Item = $ultimoitem;
                $CotizacionProductoDetalle->save();
                $ultimoitem++;
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                CotizacionProductoDetalle::where('Cotizacion', $cotizacion)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Descripcion' => $request->descripcionproductopartida [$key],
                    'Cantidad' =>  $request->cantidadpartida [$key],
                    'Precio' =>  $request->preciopartida [$key],
                    'PrecioNeto' => $request->totalpesospartida [$key],
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
                    'CostoDeLista' => $request->costolistapartida [$key],
                    'TipoDeCambio' => $request->tipocambiopartida [$key],
                    'Existencias' => $request->existenciaactualpartida [$key],
                    'InteresMeses' =>  $request->mesespartida [$key],
                    'InteresTasa' =>  $request->tasainterespartida  [$key],
                    'InteresMonto' =>  $request->montointerespartida  [$key]
                ]);
            }
        }
        return response()->json($CotizacionProducto);
    }

    //buscar folio on key up
    public function cotizaciones_productos_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = CotizacionProducto::where('Cotizacion', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
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
    public function cotizaciones_productos_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $cotizacionesproductos = CotizacionProducto::whereIn('Cotizacion', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cotizacionesproductos = CotizacionProducto::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($cotizacionesproductos as $cp){
            $data=array();
            $cotizacionesproductosdetalle = CotizacionProductoDetalle::where('Cotizacion', $cp->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesproductosdetalle as $cpd){
                $producto = Producto::where('Codigo', $cpd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cpd->Cantidad),
                    "codigodetalle"=>$cpd->Codigo,
                    "descripciondetalle"=>$cpd->Descripcion,
                    "existenciasdetalle"=>$cpd->Existencias,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cpd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cpd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cpd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cp->Cliente)->first();
            $data[]=array(
                      "cotizacionproducto"=>$cp,
                      "descuentocotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Descuento),
                      "subtotalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->SubTotal),
                      "ivacotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Iva),
                      "totalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.cotizacionesproductos.formato_pdf_cotizacionesproductos', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$cp->Cotizacion.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF)); 
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($cotizacionesproductos as $cotp){
            $ArchivoPDF = "PDF".$cotp->Cotizacion.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("CotizacionesProducto.pdf", "browser");//mostrarlos en el navegador
    }

    //generacion de formato en PDF
    public function cotizaciones_productos_generar_pdfs_indiv($documento){
        $cotizacionesproductos = CotizacionProducto::where('Cotizacion', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesproductos as $cp){
            $cotizacionesproductosdetalle = CotizacionProductoDetalle::where('Cotizacion', $cp->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesproductosdetalle as $cpd){
                $producto = Producto::where('Codigo', $cpd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cpd->Cantidad),
                    "codigodetalle"=>$cpd->Codigo,
                    "descripciondetalle"=>$cpd->Descripcion,
                    "existenciasdetalle"=>$cpd->Existencias,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cpd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cpd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cpd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cp->Cliente)->first();
            $data[]=array(
                      "cotizacionproducto"=>$cp,
                      "descuentocotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Descuento),
                      "subtotalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->SubTotal),
                      "ivacotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Iva),
                      "totalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesproductos.formato_pdf_cotizacionesproductos', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function cotizaciones_productos_obtener_datos_envio_email(Request $request){
        $cotizacionproducto = CotizacionProducto::where('Cotizacion', $request->documento)->first();
        $cliente = Cliente::where('Numero',$cotizacionproducto->Cliente)->first();
        $data = array(
            'cotizacionproducto' => $cotizacionproducto,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1,
            'email2cc' => $cliente->Email2,
            'email3cc' => $cliente->Email3
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function cotizaciones_productos_enviar_pdfs_email(Request $request){
        $cotizacionesproductos = CotizacionProducto::where('Cotizacion', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesproductos as $cp){
            $cotizacionesproductosdetalle = CotizacionProductoDetalle::where('Cotizacion', $cp->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesproductosdetalle as $cpd){
                $producto = Producto::where('Codigo', $cpd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cpd->Cantidad),
                    "codigodetalle"=>$cpd->Codigo,
                    "descripciondetalle"=>$cpd->Descripcion,
                    "existenciasdetalle"=>$cpd->Existencias,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cpd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cpd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cpd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cp->Cliente)->first();
            $data[]=array(
                      "cotizacionproducto"=>$cp,
                      "descuentocotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Descuento),
                      "subtotalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->SubTotal),
                      "ivacotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Iva),
                      "totalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Total),
                      "cliente" => $cliente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesproductos.formato_pdf_cotizacionesproductos', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = CotizacionProducto::where('Cotizacion', $request->emaildocumento)->first();
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $arraycc = array();
            array_push($arraycc, $request->emailpara);
            if($request->email2cc != ""){
                array_push($arraycc, $request->email2cc);
            }
            if($request->email3cc != ""){
                array_push($arraycc, $request->email3cc);
            }
            if($this->correodefault1enviodocumentos != ""){
                array_push($arraycc, $this->correodefault1enviodocumentos);
            }
            if($this->correodefault2enviodocumentos != ""){
                array_push($arraycc, $this->correodefault2enviodocumentos);
            }
            //subir archivo arjunto 1 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto = $request->archivoadjunto;
                $nombre_original_archivo_adjunto = $archivoadjunto->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto = time().$nombre_original_archivo_adjunto;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto);
                $urlarchivoadjunto = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto);
            }
            //subir archivo arjunto 2 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto2 != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto2 = $request->archivoadjunto2;
                $nombre_original_archivo_adjunto2 = $archivoadjunto2->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto2 = time().$nombre_original_archivo_adjunto2;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto2->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto2);
                $urlarchivoadjunto2 = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto2);
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto)
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else if($request->archivoadjunto != null && $request->archivoadjunto2 == null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
            }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoNo".$emaildocumento.".pdf");
                });
            }            
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
    public function cotizaciones_productos_generar_pdfs_cliente_indiv($documento){
        $cotizacionesproductos = CotizacionProducto::where('Cotizacion', $documento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesproductos as $cp){
            $cotizacionesproductosdetalle = CotizacionProductoDetalle::where('Cotizacion', $cp->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesproductosdetalle as $cpd){
                $producto = Producto::where('Codigo', $cpd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cpd->Cantidad),
                    "codigodetalle"=>$cpd->Codigo,
                    "descripciondetalle"=>$cpd->Descripcion,
                    "existenciasdetalle"=>$cpd->Existencias,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cpd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cpd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cpd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cp->Cliente)->first();
            $data[]=array(
                    "cotizacionproducto"=>$cp,
                    "descuentocotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Descuento),
                    "subtotalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->SubTotal),
                    "ivacotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Iva),
                    "totalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Total),
                    "cliente" => $cliente,
                    "fechaformato"=> $fechaformato,
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesproductos.formato_pdf_cliente_cotizacionesproductos', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //enviar pdf por emial
    public function cotizaciones_productos_enviar_pdfs_cliente_email(Request $request){
        $cotizacionesproductos = CotizacionProducto::where('Cotizacion', $request->emaildocumento)->orderBy('Folio', 'ASC')->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cotizacionesproductos as $cp){
            $cotizacionesproductosdetalle = CotizacionProductoDetalle::where('Cotizacion', $cp->Cotizacion)->get();
            $datadetalle=array();
            foreach($cotizacionesproductosdetalle as $cpd){
                $producto = Producto::where('Codigo', $cpd->Codigo)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cpd->Cantidad),
                    "codigodetalle"=>$cpd->Codigo,
                    "descripciondetalle"=>$cpd->Descripcion,
                    "existenciasdetalle"=>$cpd->Existencias,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cpd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cpd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cpd->SubTotal)
                );
            } 
            $cliente = Cliente::where('Numero', $cp->Cliente)->first();
            $data[]=array(
                    "cotizacionproducto"=>$cp,
                    "descuentocotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Descuento),
                    "subtotalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->SubTotal),
                    "ivacotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Iva),
                    "totalcotizacionproducto"=>Helpers::convertirvalorcorrecto($cp->Total),
                    "cliente" => $cliente,
                    "fechaformato"=> $fechaformato,
                    "datadetalle" => $datadetalle,
                    "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cotizacionesproductos.formato_pdf_cliente_cotizacionesproductos', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = CotizacionProducto::where('Cotizacion', $request->emaildocumento)->first();
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $arraycc = array();
            array_push($arraycc, $request->emailpara);
            if($request->email2cc != ""){
                array_push($arraycc, $request->email2cc);
            }
            if($request->email3cc != ""){
                array_push($arraycc, $request->email3cc);
            }
            if($this->correodefault1enviodocumentos != ""){
                array_push($arraycc, $this->correodefault1enviodocumentos);
            }
            if($this->correodefault2enviodocumentos != ""){
                array_push($arraycc, $this->correodefault2enviodocumentos);
            }
            //subir archivo arjunto 1 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto = $request->archivoadjunto;
                $nombre_original_archivo_adjunto = $archivoadjunto->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto = time().$nombre_original_archivo_adjunto;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto);
                $urlarchivoadjunto = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto);
            }
            //subir archivo arjunto 2 en public/archivos_adjuntos para poder adjuntarlo en el correo
            if($request->archivoadjunto2 != null) {
                //archivos adjuntos
                $mover_a_carpeta="archivos_adjuntos";
                $archivoadjunto2 = $request->archivoadjunto2;
                $nombre_original_archivo_adjunto2 = $archivoadjunto2->getClientOriginalName();
                $nuevo_nombre_archivo_adjunto2 = time().$nombre_original_archivo_adjunto2;
                //guardar archivos en public/archivos_adjuntos
                $archivoadjunto2->move($mover_a_carpeta, $nuevo_nombre_archivo_adjunto2);
                $urlarchivoadjunto2 = public_path('archivos_adjuntos\\'.$nuevo_nombre_archivo_adjunto2);
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoClienteNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto)
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else if($request->archivoadjunto != null && $request->archivoadjunto2 == null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoClienteNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
            }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoClienteNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CotizacionProductoClienteNo".$emaildocumento.".pdf");
                });
            }        
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
    public function cotizaciones_productos_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new CotizacionesProductosExport($this->campos_consulta,$request->periodo), "cotizacionesproductos-".$request->periodo.".xlsx");   
    }
    //configurar tabla
    public function cotizaciones_productos_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        Configuracion_Tabla::where('tabla', 'CotizacionesProductos')
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
        return redirect()->route('cotizaciones_productos');
    }

}
