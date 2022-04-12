<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlantillasRequisicionesExport;
use App\Imports\RequisicionesImport;
use App\Exports\RequisicionesExport;
use App\Requisicion;
use App\RequisicionDetalle;
use App\Traspaso;
use App\TraspasoDetalle;
use App\TipoOrdenCompra;
use App\Cliente;
use App\Almacen;
use App\Departamento;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Producto;
use App\BitacoraDocumento;
use App\Existencia;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaRequisicion;
use App\VistaObtenerExistenciaProducto;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Serie;
use App\Firma_Rel_Documento;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 
use ZipArchive;

class RequisicionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function requisiciones(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Requisiciones', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('requisiciones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('requisiciones_exportar_excel');
        $rutacreardocumento = route('requisiciones_generar_pdfs');
        $almacendedefault = Almacen::where('Numero', 1)->first();
        $urlgenerarplantilla = route('requisiciones_generar_plantilla');
        return view('registros.requisiciones.requisiciones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','almacendedefault','urlgenerarplantilla'));
    }

    public function requisiciones_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Requisiciones', Auth::user()->id);
            $periodo = $request->periodo;
            $data = VistaRequisicion::select($configuraciones_tabla['campos_consulta'])->where('periodo', $periodo);
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
                    ->withQuery('sumaimporte', function($data) {
                        return $data->sum('Importe');
                    })
                    ->withQuery('sumadescuento', function($data) {
                        return $data->sum('Descuento');
                    })
                    ->withQuery('sumasubtotal', function($data) {
                        return $data->sum('SubTotal');
                    })
                    ->withQuery('sumaiva', function($data) {
                        return $data->sum('Iva');
                    })
                    ->withQuery('sumatotal', function($data) {
                        return $data->sum('Total');
                    })
                    ->withQuery('sumacosto', function($data) {
                        return $data->sum('Costo');
                    })
                    ->withQuery('sumacomision', function($data) {
                        return $data->sum('Comision');
                    })
                    ->withQuery('sumautilidad', function($data) {
                        return $data->sum('Utilidad');
                    })
                    ->addColumn('operaciones', function($data){
                        $operaciones = '<div class="dropdown">'.
                                    '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                        'OPERACIONES <span class="caret"></span>'.
                                    '</button>'.
                                    '<ul class="dropdown-menu">'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Requisicion .'\')">Cambios</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Requisicion .'\')">Bajas</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="'.route('requisiciones_generar_pdfs_indiv',$data->Requisicion).'" target="_blank">Ver Documento PDF</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Requisicion .'\')">Enviar Documento por Correo</a></li>'.
                                    '</ul>'.
                                '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    //->addColumn('subtotal', function($data){ return $data->SubTotal; })
                    //->addColumn('iva', function($data){ return $data->Iva; })
                    //->addColumn('total', function($data){ return $data->Total; })
                    //->addColumn('importe', function($data){ return $data->Importe; })
                    //->addColumn('descuento', function($data){ return $data->Descuento; })
                    //->addColumn('costo', function($data){ return $data->Costo; })
                    //->addColumn('comision', function($data){ return $data->Comision; })
                    //->addColumn('utilidad', function($data){ return $data->Utilidad; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //descargar plantilla
    public function requisiciones_generar_plantilla(){
        return Excel::download(new PlantillasRequisicionesExport(), "plantillarequisiciones.xlsx"); 
    }
    //cargar partidas excel
    public function requisiciones_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new RequisicionesImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesrequisicion = '';
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
                        $filasdetallesrequisicion= $filasdetallesrequisicion.
                        '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                            '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$producto->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.htmlspecialchars($producto->Producto, ENT_QUOTES).'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]">'.$producto->Unidad.'</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl observacionespartida" name="observacionespartida[]" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
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
            "filasdetallesrequisicion" => $filasdetallesrequisicion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data); 
    }
    //obtener series documento
    public function requisiciones_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Requisiciones')->where('Usuario', Auth::user()->user)->get();
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
    public function requisiciones_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Requisicion',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function requisiciones_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Requisicion',$request->serie);
        return response()->json($folio);
    }
    //obtener ordenes trabajo
    public function requisiciones_obtener_ordenes_trabajo(Request $request){
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
    public function requisiciones_obtener_orden_trabajo_por_folio(Request $request){
        $orden = '';
        $fecha = '';
        $cliente = '';
        $tipo = '';
        $unidad = '';
        $statusorden = '';
        $existeorden = DB::table('Ordenes de Trabajo as ot')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                            ->where('ot.Status', 'ABIERTA')
                            ->where('ot.Orden', $request->orden)
                            ->count();
        if($existeorden > 0){
            $ot = DB::table('Ordenes de Trabajo as ot')
                        ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                        ->select('ot.Orden as Orden', 'ot.Fecha as Fecha', 'c.Nombre as Cliente', 'ot.Tipo as Tipo', 'ot.Unidad as Unidad', 'ot.Status AS StatusOrden')
                        ->where('ot.Status', 'ABIERTA')
                        ->where('ot.Orden', $request->orden)
                        ->get();
            $orden = $ot[0]->Orden;
            $fecha = $ot[0]->Fecha;
            $cliente = $ot[0]->Cliente;
            $tipo = $ot[0]->Tipo;
            $unidad = $ot[0]->Unidad;
            $statusorden = $ot[0]->StatusOrden;
        }
        $data = array(
            'orden' => $orden,
            'fecha' => $fecha,
            'cliente' => $cliente,
            'tipo' => $tipo,
            'unidad' => $unidad,
            'statusorden' => $statusorden
        );
        return response()->json($data); 
    }

    //obtener productos
    public function requisiciones_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')
                                                    ->where(function ($query) {
                                                        $query->where('Almacen', 1)
                                                        ->orWhere('Almacen', NULL);
                                                    });
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\',\''.$tipooperacion.'\')">Seleccionar</div>';
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
    public function requisiciones_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                            ->where(function ($query) {
                                                                $query->where('Almacen', 1)
                                                                ->orWhere('Almacen', NULL);
                                                            })->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)
                                                        ->where(function ($query) {
                                                            $query->where('Almacen', 1)
                                                            ->orWhere('Almacen', NULL);
                                                        })->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
                'Existencias' => Helpers::convertirvalorcorrecto($producto->Existencias),
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
                'CostoDeLista' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);        
    }
    //altas
    public function requisiciones_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo folio de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\Requisicion',$request->serie);
        //INGRESAR DATOS A TABLA ORDEN COMPRA
        $requisicion = $folio.'-'.$request->serie;
        $Requisicion = new Requisicion;
        $Requisicion->Requisicion=$requisicion;
        $Requisicion->Serie=$request->serie;
        $Requisicion->Folio=$folio;
        $Requisicion->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Requisicion->Orden=$request->orden;
        $Requisicion->Importe=$request->importe;
        $Requisicion->Descuento=$request->descuento;
        $Requisicion->SubTotal=$request->subtotal;
        $Requisicion->Iva=$request->iva;
        $Requisicion->Total=$request->total;
        $Requisicion->Costo=$request->costo;
        $Requisicion->Comision=$request->comision;
        $Requisicion->Utilidad=$request->utilidad;
        $Requisicion->Obs=$request->observaciones;
        $Requisicion->Status="POR SURTIR";
        $Requisicion->Usuario=Auth::user()->user;
        $Requisicion->Periodo=$this->periodohoy;
        $Requisicion->save();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REQUISICIONES";
        $BitacoraDocumento->Movimiento = $requisicion;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR SURTIR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $item = 1;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
            $RequisicionDetalle=new RequisicionDetalle;
            $RequisicionDetalle->Requisicion = $requisicion;
            $RequisicionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $RequisicionDetalle->Codigo = $codigoproductopartida;
            $RequisicionDetalle->Descripcion = $request->descripcionproductopartida [$key];
            $RequisicionDetalle->Unidad = $request->unidadproductopartida [$key];
            $RequisicionDetalle->Cantidad =  $request->cantidadpartida [$key];
            $RequisicionDetalle->Precio =  $request->preciopartida [$key];
            $RequisicionDetalle->Importe =  $request->importepartida [$key];
            $RequisicionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
            $RequisicionDetalle->Descuento =  $request->descuentopesospartida  [$key];
            $RequisicionDetalle->SubTotal =  $request->subtotalpartida [$key];
            $RequisicionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
            $RequisicionDetalle->Iva =  $request->ivapesospartida [$key];
            $RequisicionDetalle->Total =  $request->totalpesospartida [$key];
            $RequisicionDetalle->Costo =  $request->costopartida [$key];
            $RequisicionDetalle->CostoTotal =  $request->costototalpartida [$key];
            $RequisicionDetalle->Com =  $request->comisionporcentajepartida [$key];
            $RequisicionDetalle->Comision =  $request->comisionpesospartida [$key];
            $RequisicionDetalle->Utilidad =  $request->utilidadpartida [$key];
            $RequisicionDetalle->Surtir =  $request->porsurtirpartida [$key];
            $RequisicionDetalle->Registro =  0;
            $RequisicionDetalle->Obs =  $request->observacionespartida [$key];
            $RequisicionDetalle->Moneda =  $request->monedapartida [$key];
            $RequisicionDetalle->CostoDeLista =  $request->costodelistapartida [$key];
            $RequisicionDetalle->TipoDeCambio =  $request->tipodecambiopartida [$key];
            $RequisicionDetalle->Item = $item;
            $RequisicionDetalle->save();
            $item++;
        }
        return response()->json($Requisicion);
    }
    //verificar si la requisicion ya fue utilizada en un traspaso
    public function requisiciones_verificar_baja(Request $request){
        $Requisicion = Requisicion::where('Requisicion', $request->requisiciondesactivar)->first();
        $resultado = TraspasoDetalle::where('Requisicion', $request->requisiciondesactivar)->count();
        $numerotraspaso = 0;
        if($resultado > 0){
            $detalletraspaso= TraspasoDetalle::where('Requisicion', $request->requisiciondesactivar)->first();
            $numerotraspaso = $detalletraspaso->Traspaso;
        }
        $resultadofechas = Helpers::compararanoymesfechas($Requisicion->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'resultado' => $resultado,
            'numerotraspaso' => $numerotraspaso,
            'Status' => $Requisicion->Status
        );
        return response()->json($data);
    }
    //dar de baja requisicion
    public function requisiciones_alta_o_baja(Request $request){
        $Requisicion = Requisicion::where('Requisicion', $request->requisiciondesactivar)->first();
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Requisicion::where('Requisicion', $request->requisiciondesactivar)
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
        $detalles = RequisicionDetalle::where('Requisicion', $request->requisiciondesactivar)->get();
        foreach($detalles as $detalle){
            //colocar en ceros cantidades
            RequisicionDetalle::where('Requisicion', $request->requisiciondesactivar)
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
                                'Utilidad' => '0.000000',
                                'Surtir' => '0.000000',
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REQUISICIONES";
        $BitacoraDocumento->Movimiento = $request->requisiciondesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Requisicion->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Requisicion);
    }

    //obtener registro
    public function requisiciones_obtener_requisicion(Request $request){
        $requisicion = Requisicion::where('Requisicion', $request->requisicionmodificar)->first();
        $ordentrabajo = OrdenTrabajo::where('Orden', $requisicion->Orden)->first();
        $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
        $fechaorden = Helpers::formatoinputdatetime($ordentrabajo->Fecha);
        //detalles
        $detallesrequisicion= RequisicionDetalle::where('Requisicion', $request->requisicionmodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesrequisicion = RequisicionDetalle::where('Requisicion', $request->requisicionmodificar)->count();
        if($numerodetallesrequisicion > 0){
            $filasdetallesrequisicion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallesrequisicion as $dr){
                $producto = Producto::where('Codigo', $dr->Codigo)->first();
                $filasdetallesrequisicion= $filasdetallesrequisicion.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dr->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" value="'.$dr->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dr->Unidad.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida" name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadopreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Utilidad).'" data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl observacionespartida" name="observacionespartida[]" value="'.$dr->Obs.'" data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dr->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipodecambiopartida" name="tipodecambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->TIpoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;
            }
        }else{
            $filasdetallesrequisicion = '';
        }   
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($requisicion->Status == 'SURTIDO' || $requisicion->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($requisicion->Status == 'SURTIDO' || $requisicion->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($requisicion->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }    
        $data = array(
            "requisicion" => $requisicion,
            "ordentrabajo" => $ordentrabajo,
            "cliente" => $cliente,
            "filasdetallesrequisicion" => $filasdetallesrequisicion,
            "numerodetallesrequisicion" => $numerodetallesrequisicion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "fecha" => Helpers::formatoinputdatetime($requisicion->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($requisicion->Fecha),
            "fechaorden" => $fechaorden,
            "importe" => Helpers::convertirvalorcorrecto($requisicion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($requisicion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($requisicion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($requisicion->Iva),
            "total" => Helpers::convertirvalorcorrecto($requisicion->Total),
            "costo" => Helpers::convertirvalorcorrecto($requisicion->Costo),
            "comision" => Helpers::convertirvalorcorrecto($requisicion->Comision),
            "utilidad" => Helpers::convertirvalorcorrecto($requisicion->Utilidad),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //guardar modificacion 
    public function requisiciones_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $requisicion = $request->folio.'-'.$request->serie;
        $Requisicion = Requisicion::where('Requisicion', $requisicion)->first();
        //validar si las partidas en las modiifcacion son las mismas que los detalles
        // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles
        //array partidas antes de modificacion
        $ArrayDetallesRequisicionAnterior = Array();
        $DetallesRequisicionAnterior = RequisicionDetalle::where('Requisicion', $requisicion)->get();
        foreach($DetallesRequisicionAnterior as $detalle){
            array_push($ArrayDetallesRequisicionAnterior, $detalle->Requisicion.'#'.$detalle->Codigo.'#'.$detalle->Item);
        }
        //array partida despues de modificacion
        $ArrayDetallesRequisicionNuevo = Array();
        foreach ($request->codigoproductopartida as $key => $nuevocodigo){
            if($request->agregadoen [$key] == 'NA'){
                array_push($ArrayDetallesRequisicionNuevo, $requisicion.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
            } 
        }  
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesRequisicionAnterior, $ArrayDetallesRequisicionNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle de la remision eliminado
                $eliminardetallerequisicion = RequisicionDetalle::where('Requisicion', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
            }
        }
        //modificar orden compra
        Requisicion::where('Requisicion', $requisicion)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Importe' => $request->importe,
            'Descuento' => $request->descuento,
            'SubTotal' => $request->subtotal,
            'Iva' => $request->iva,
            'Total' => $request->total,
            'Costo' => $request->costo,
            'Comision' => $request->comision,
            'Utilidad' => $request->utilidad,
            'Obs' => $request->observaciones
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "REQUISICIONES";
        $BitacoraDocumento->Movimiento = $requisicion;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Requisicion->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
        $detallesporsurtir = 0;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){    
            //if la partida se agrego en la modificacion se agrega en los detalles
            if($request->agregadoen [$key] == 'modificacion'){      
                $contaritems = RequisicionDetalle::select('Item')->where('Requisicion', $requisicion)->count();
                if($contaritems > 0){
                    $item = RequisicionDetalle::select('Item')->where('Requisicion', $requisicion)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
                }else{
                    $ultimoitem = 1;
                }
                $RequisicionDetalle=new RequisicionDetalle;
                $RequisicionDetalle->Requisicion = $requisicion;
                $RequisicionDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $RequisicionDetalle->Codigo = $codigoproductopartida;
                $RequisicionDetalle->Descripcion = $request->descripcionproductopartida [$key];
                $RequisicionDetalle->Unidad = $request->unidadproductopartida [$key];
                $RequisicionDetalle->Cantidad =  $request->cantidadpartida [$key];
                $RequisicionDetalle->Precio =  $request->preciopartida [$key];
                $RequisicionDetalle->Importe =  $request->importepartida [$key];
                $RequisicionDetalle->Dcto =  $request->descuentoporcentajepartida [$key];
                $RequisicionDetalle->Descuento =  $request->descuentopesospartida  [$key];
                $RequisicionDetalle->SubTotal =  $request->subtotalpartida [$key];
                $RequisicionDetalle->Impuesto =  $request->ivaporcentajepartida [$key];
                $RequisicionDetalle->Iva =  $request->ivapesospartida [$key];
                $RequisicionDetalle->Total =  $request->totalpesospartida [$key];
                $RequisicionDetalle->Costo =  $request->costopartida [$key];
                $RequisicionDetalle->CostoTotal =  $request->costototalpartida [$key];
                $RequisicionDetalle->Com =  $request->comisionporcentajepartida [$key];
                $RequisicionDetalle->Comision =  $request->comisionpesospartida [$key];
                $RequisicionDetalle->Utilidad =  $request->utilidadpartida [$key];
                $RequisicionDetalle->Surtir =  $request->porsurtirpartida [$key];
                $RequisicionDetalle->Registro =  0;
                $RequisicionDetalle->Obs =  $request->observacionespartida [$key];
                $RequisicionDetalle->Moneda =  $request->monedapartida [$key];
                $RequisicionDetalle->CostoDeLista =  $request->costodelistapartida [$key];
                $RequisicionDetalle->TipoDeCambio =  $request->tipodecambiopartida [$key];
                $RequisicionDetalle->Item = $ultimoitem;
                $RequisicionDetalle->save();
                $ultimoitem++;   
            }else{
                //si la partida no se agrego en la modificacion solo se modifican los datos
                //modificar detalle
                //obtener surtir
                $cantidadsurtidatraspasos = 0;
                $numerodetallestraspaso = TraspasoDetalle::where('Requisicion', $requisicion)->where('Codigo', $codigoproductopartida)->count();
                if($numerodetallestraspaso > 0){
                    $detallestraspaso = TraspasoDetalle::where('Requisicion', $requisicion)->where('Codigo', $codigoproductopartida)->get();
                    foreach($detallestraspaso as $dt){
                        $cantidadsurtidatraspasos = $cantidadsurtidatraspasos + $dt->Cantidad;
                    }
                }
                $surtir = $request->porsurtirpartida [$key] - $cantidadsurtidatraspasos;
                RequisicionDetalle::where('Requisicion', $requisicion)
                ->where('Item', $request->itempartida [$key])
                ->update([
                    'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Descripcion' => $request->descripcionproductopartida [$key],
                    'Unidad' => $request->unidadproductopartida [$key],
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
                    'Comision' =>  $request->comisionpesospartida [$key],
                    'Utilidad' =>  $request->utilidadpartida [$key],
                    'Surtir' =>  Helpers::convertirvalorcorrecto($surtir),
                    'Obs' =>  $request->observacionespartida [$key],
                    'Moneda' =>  $request->monedapartida [$key],
                    'CostoDeLista' =>  $request->costodelistapartida [$key],
                    'TipoDeCambio' =>  $request->tipodecambiopartida [$key]
                ]);
            }
            //verificar si la partida ya esta surtida
            if($cantidadsurtidatraspasos > 0){
                $detallesporsurtir++;//aun no se termina de surtir
            }
        }
        //Cerrar la orden de compra si todas sus partidas tienen cero en por surtir
        if($detallesporsurtir == 0){
            Requisicion::where('Requisicion', $requisicion)
                        ->update([
                            'TipoDeCambio' =>  'SURTIDO'
                        ]);
        }
    	return response()->json($Requisicion);     
    }


    //buscar folio
    public function requisiciones_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaRequisicion::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        } 
    }
    //generar documento pdf
    public function requisiciones_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $requisiciones = Requisicion::whereIn('Requisicion', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            if ($request->has("seriesdisponiblesdocumento")){
                $requisiciones = Requisicion::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(1500)->get();
            }else{
                $requisiciones = Requisicion::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($requisiciones as $r){
            $data=array();
            $requisiciondetalle = RequisicionDetalle::where('Requisicion', $r->Requisicion)->get();
            $datadetalle=array();
            foreach($requisiciondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $linea = Marca::where('Numero', $producto->Linea)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "lineadetalle"=>$linea->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "importedetalle" => Helpers::convertirvalorcorrecto($rd->Importe)
                );
            } 
            $ordentrabajo = OrdenTrabajo::where('Orden', $r->Orden)->first();
            $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Requisiciones')->where('Documento', $r->Requisicion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Requisiciones')
            ->where('frd.Documento', $r->Requisicion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "requisicion"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cliente" =>  $cliente,
                      "ordentrabajo" => $ordentrabajo,
                      "totalrequisicion"=>Helpers::convertirvalorcorrecto($r->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.requisiciones.formato_pdf_requisiciones', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$r->Requisicion.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($requisiciones as $req){
            $ArchivoPDF = "PDF".$req->Requisicion.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->descargar_xml == 0){
            $pdfMerger->save("Requisiciones.pdf", "browser");//mostrarlos en el navegador
        }else{
            //carpeta donde se guardara el archivo zip
            $public_dir=public_path();
            // Zip File Name
            $zipFileName = 'DocumentosPDF.zip';
            // Crear Objeto ZipArchive
            $zip = new ZipArchive;
            if ($zip->open($public_dir . '/xml_descargados/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
                // Agregar archivos que se comprimiran
                foreach($arrayfilespdf as $afp) {
                    $zip->addFile(Storage::disk('local3')->getAdapter()->applyPathPrefix($afp),$afp);
                }     
                //terminar proceso   
                $zip->close();
            }
            // Set Encabezados para descargar
            $headers = array(
                'Content-Type' => 'application/octet-stream',
            );
            $filetopath=$public_dir.'/xml_descargados/'.$zipFileName;
            // Create Download Response
            if(file_exists($filetopath)){
                return response()->download($filetopath,$zipFileName,$headers);
            }
        }
    }
    //generacion de formato en PDF
    public function requisiciones_generar_pdfs_indiv($documento){
        $requisiciones = Requisicion::where('Requisicion', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($requisiciones as $r){
            $requisiciondetalle = RequisicionDetalle::where('Requisicion', $r->Requisicion)->get();
            $datadetalle=array();
            foreach($requisiciondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $linea = Marca::where('Numero', $producto->Linea)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "lineadetalle"=>$linea->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "importedetalle" => Helpers::convertirvalorcorrecto($rd->Importe)
                );
            } 
            $ordentrabajo = OrdenTrabajo::where('Orden', $r->Orden)->first();
            $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Requisiciones')->where('Documento', $r->Requisicion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Requisiciones')
            ->where('frd.Documento', $r->Requisicion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "requisicion"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cliente" =>  $cliente,
                      "ordentrabajo" => $ordentrabajo,
                      "totalrequisicion"=>Helpers::convertirvalorcorrecto($r->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.requisiciones.formato_pdf_requisiciones', compact('data'))
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
    public function requisiciones_obtener_datos_envio_email(Request $request){
        $requisicion = Requisicion::where('Requisicion', $request->documento)->first();
        $ordent = OrdenTrabajo::where('Orden', $requisicion->Orden)->first();
        $cliente = Cliente::where('Numero',$ordent->Cliente)->first();
        $email2cc = '';
        $email3cc = '';
        if($cliente->Email2 != '' || $cliente->Email2 != null){
            $email2cc = $cliente->Email2;
        }
        if($cliente->Email3 != '' || $cliente->Email3 != null){
            $email3cc = $cliente->Email3;
        }
        $data = array(
            'requisicion' => $requisicion,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }
    //enviar pdf por emial
    public function requisiciones_enviar_pdfs_email(Request $request){
        $requisiciones = Requisicion::where('Requisicion', $request->emaildocumento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($requisiciones as $r){
            $requisiciondetalle = RequisicionDetalle::where('Requisicion', $r->Requisicion)->get();
            $datadetalle=array();
            foreach($requisiciondetalle as $rd){
                $producto = Producto::where('Codigo', $rd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $linea = Marca::where('Numero', $producto->Linea)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd->Cantidad),
                    "codigodetalle"=>$rd->Codigo,
                    "descripciondetalle"=>$rd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "lineadetalle"=>$linea->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($rd->Precio),
                    "importedetalle" => Helpers::convertirvalorcorrecto($rd->Importe)
                );
            } 
            $ordentrabajo = OrdenTrabajo::where('Orden', $r->Orden)->first();
            $cliente = Cliente::where('Numero', $ordentrabajo->Cliente)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Requisiciones')->where('Documento', $r->Requisicion)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Requisiciones')
            ->where('frd.Documento', $r->Requisicion)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "requisicion"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "cliente" =>  $cliente,
                      "ordentrabajo" => $ordentrabajo,
                      "totalrequisicion"=>Helpers::convertirvalorcorrecto($r->Total),
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.requisiciones.formato_pdf_requisiciones', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = Requisicion::where('Requisicion', $request->emaildocumento)->first();
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
            if($request->correosconcopia != null){
                foreach($request->correosconcopia as $cc){
                    if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                        array_push($arraycc, $cc);
                    }
                }
            }
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                        ->cc($arraycc)
                        ->subject($asunto)
                        ->attachData($pdf->output(), "RequisicionNo".$emaildocumento.".pdf");
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
    //exportar excel
    public function requisiciones_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Requisiciones', Auth::user()->id);
        return Excel::download(new RequisicionesExport($configuraciones_tabla['campos_consulta'],$request->periodo), "requisciones-".$request->periodo.".xlsx");   
    }
    //guardar configuracion tabla
    public function requisiciones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Requisiciones', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Requisiciones')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Requisiciones';
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
        return redirect()->route('requisiciones');
    }
}
