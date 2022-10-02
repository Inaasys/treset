<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Helpers;
use DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComprasExport;
use App\Exports\PlantillaCompraExport;
use App\Exports\ComprasDetallesExport;
use App\Compra;
use App\CompraDetalle;
use App\TipoOrdenCompra;
use App\Proveedor;
use App\Almacen;
use App\OrdenCompra;
use App\OrdenCompraDetalle;
use App\Departamento;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Producto;
use App\BitacoraDocumento;
use App\Existencia;
use App\CuentaXPagar;
use App\CuentaXPagarDetalle;
use App\NotaProveedor;
use App\NotaProveedorDetalle;
use App\NotaProveedorDocumento;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaCompra;
use App\VistaObtenerExistenciaProducto;
use App\ContraRecibo;
use App\ContraReciboDetalle;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Serie;
use App\Firma_Rel_Documento;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use ZipArchive;
use File;
use FastExcel;

class CompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function compras(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Compras', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('compras_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('compras_exportar_excel');
        $urlgenerarformatoexceldetalles = route('compra_detalles_exportar_excel');
        $rutacreardocumento = route('compras_generar_pdfs');
        return view('registros.compras.compras', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','urlgenerarformatoexceldetalles','rutacreardocumento'));
    }
    //obtener todos los registros
    public function compras_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Compras', Auth::user()->id);
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCompra::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                    ->withQuery('sumaabonos', function($data) {
                        return $data->sum('Abonos');
                    })
                    ->withQuery('sumadescuentos', function($data) {
                        return $data->sum('Descuentos');
                    })
                    ->withQuery('sumasaldo', function($data) {
                        return $data->sum('Saldo');
                    })
                    ->addColumn('operaciones', function($data) use ($fechahoy,$tipousuariologueado){
                        $operaciones = '<div class="dropdown">'.
                                    '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                        'OPERACIONES <span class="caret"></span>'.
                                    '</button>'.
                                    '<ul class="dropdown-menu">'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Compra.'\')">Cambios</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Compra.'\')">Bajas</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="movimientoscompra(\''.$data->Compra.'\')">Movimientos</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="'.route('compras_generar_pdfs_indiv',$data->Compra).'" target="_blank">Ver Documento PDF</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Compra .'\')">Enviar Documento por Correo</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="'.route('compras_generar_excel_indiv',$data->Compra).'" target="_blank">Generar Excel</a></li>'.
                                        '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Compra .'\')">Imprimir Documento PDF</a></li>'.
                                    '</ul>'.
                                '</div>';
                        return $operaciones;
                    })
                    ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                    //->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    //->addColumn('Iva', function($data){ return $data->Iva; })
                    //->addColumn('Total', function($data){ return $data->Total; })
                    //->addColumn('Abonos', function($data){ return $data->Abonos; })
                    //->addColumn('Descuentos', function($data){ return $data->Descuentos; })
                    //->addColumn('Saldo', function($data){ return $data->Saldo; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    //->addColumn('Descuento', function($data){ return $data->Descuento; })
                    //->addColumn('Importe', function($data){ return $data->Importe; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener series documento
    public function compras_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Compras')->where('Usuario', Auth::user()->user)->get();
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
    public function compras_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Compra',$request->Serie);
        return response()->json($folio);
    }
    //obtener el ultimo folio de la tabla
    public function compras_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Compra',$request->serie);
        return response()->json($folio);
    }
    //obtener tipos ordenes de compra
    public function compras_obtener_tipos_ordenes_compra(Request $request){
        $almacen = $request->almacen;
        switch ($request->tipoalta) {
            case "GASTOS":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'GASTOS')->orWhere('Nombre', 'CAJA CHICA')->get();
                break;
            case "CAJA CHICA":
                if($almacen > 0){
                    $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();
                }else{
                    $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'GASTOS')->orWhere('Nombre', 'CAJA CHICA')->get();
                }
                break;
            case "TOT":
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', 'TOT')->get();
                break;
            default:
                $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();

        }
        $select_tipos_ordenes_compra = "";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //cargar xml en la alta
    public function compras_cargar_xml_alta(Request $request){
        $mover_a_carpeta="xml_cargados";
        $xml = $request->xml;
        $nombre_original = $xml->getClientOriginalName();
        //guardar xml en public/xml_cargados
        $xml->move($mover_a_carpeta,$nombre_original);
        if (file_exists('xml_cargados/'.$nombre_original)) {
            //cargar xml
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file('xml_cargados/'.$nombre_original);
            //obtener datos generales del xml nodo Comprobante
            $comprobante = $xml->attributes();
            $array_comprobante = array(
                "Total" => $comprobante['Total'],
                "Moneda" => $comprobante['Moneda'],
                "TipoDeComprobante" => $comprobante['TipoDeComprobante'],
                "MetodoPago" => $comprobante['MetodoPago'],
                "LugarExpedicion" => $comprobante['LugarExpedicion'],
                "SubTotal" => $comprobante['SubTotal'],
                "Folio" => $comprobante['Folio'],
                "Fecha" => $comprobante['Fecha'],
                "Version" => $comprobante['Version'],
                "Serie" => $comprobante['Serie'],
                "Sello" => $comprobante['Sello'],
                "Certificado" => $comprobante['Certificado'],
                "CondicionesDePago" => $comprobante['CondicionesDePago'],
                "FormaPago" => $comprobante['FormaPago'],
                "NoCertificado" => $comprobante['NoCertificado'],
                "TipoCambio" => $comprobante['TipoCambio'],
                "Descuento" => $comprobante['Descuento']
            );
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            if($namespaces->Emisor){
                $emisor = $namespaces->Emisor->attributes();
                $array_emisor = array(
                    "Rfc" => $emisor['Rfc'],
                    "Nombre" => $emisor['Nombre'],
                    "RegimenFiscal" => $emisor['RegimenFiscal']
                );
            }else{
                $emisor = "";
            }
            //obtener datos generales del xml nodo Receptor
            if($namespaces->Receptor){
                $receptor = $namespaces->Receptor->attributes();
                $array_receptor= array(
                    "Rfc" => $receptor['Rfc'],
                    "Nombre" => $receptor['Nombre'],
                    "UsoCFDI" => $receptor['UsoCFDI']
                );
            }else{
                $receptor = "";
            }
            //obtener datos generales del xml nodo Impuestos
            if(isset($namespaces->Impuestos)){
                $impuesto = $namespaces->Impuestos->attributes();
                $TotalImpuestosTrasladados = $impuesto['TotalImpuestosTrasladados'];
                //obtener datos generales del xml nodo hijo traslado del nodo padre Impuestos
                $array_traslados_impuestos = array();
                if (isset($namespaces->Impuestos->Traslados->Traslado)) {
                    foreach($namespaces->Impuestos->Traslados->Traslado as $traslado){
                        $atributos_traslado = $traslado->attributes();
                        $array_traslados_impuestos[] = array(
                            "Impuesto" => $atributos_traslado['Impuesto'],
                            "TipoFactor" => $atributos_traslado['TipoFactor'],
                            "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                            "Importe" => $atributos_traslado['Importe']
                        );
                    }
                }else{
                    $TotalImpuestosTrasladados = '0.00';
                    //obtener datos generales del xml nodo hijo traslado del nodo padre Impuestos
                    $array_traslados_impuestos = array();
                        $array_traslados_impuestos[] = array(
                            "Impuesto" => "",
                            "TipoFactor" =>"",
                            "TasaOCuota" => "0.160000",
                            "Importe" => "0.00"
                        );
                }
            }else{
                $TotalImpuestosTrasladados = '0.00';
                //obtener datos generales del xml nodo hijo traslado del nodo padre Impuestos
                $array_traslados_impuestos = array();
                    $array_traslados_impuestos[] = array(
                        "Impuesto" => "",
                        "TipoFactor" =>"",
                        "TasaOCuota" => "0.160000",
                        "Importe" => "0.00"
                );
            }
            //obtener todas las partidas ó conceptos del xml
            $array_conceptos = array();
            //detalles xml
            $filasdetallesxml = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($namespaces->Conceptos->Concepto as $concepto){
                //obtener datos generales del xml nodo hijo traslado del nodo padre Concepto
                $array_traslados = array();
                if(isset($concepto->Impuestos->Traslados->Traslado)){
                    $atributos_traslado = $concepto->Impuestos->Traslados->Traslado->attributes();
                    $array_traslados[] = array(
                        "Base" => $atributos_traslado['Base'],
                        "Impuesto" => $atributos_traslado['Impuesto'],
                        "TipoFactor" => $atributos_traslado['TipoFactor'],
                        "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                        "Importe" => $atributos_traslado['Importe']
                    );
                }else{
                    $array_traslados[] = array(
                        "Base" => '0.00',
                        "Impuesto" => '0.00',
                        "TipoFactor" => '0.00',
                        "TasaOCuota" => '0.00',
                        "Importe" => '0.00'
                    );
                }
                //obtener datos generales del xml nodo Concepto
                $atributos_concepto = $concepto->attributes();
                $array_conceptos[] = array(
                    "ClaveProdServ" => $atributos_concepto['ClaveProdServ'],
                    "Cantidad" => $atributos_concepto['Cantidad'],
                    "ClaveUnidad" => $atributos_concepto['ClaveUnidad'],
                    "Unidad" => $atributos_concepto['Unidad'],
                    "Descripcion" => $atributos_concepto['Descripcion'],
                    "ValorUnitario" => $atributos_concepto['ValorUnitario'],
                    "Importe" => $atributos_concepto['Importe'],
                    "Descuento" => $atributos_concepto['Descuento'],
                    "NoIdentificacion" => $atributos_concepto['NoIdentificacion'],
                    "array_traslados" => $array_traslados
                );
                /*
                //detalles xml
                //$producto = Producto::where('Codigo', $doc->Codigo)->first();
                $claveproductopartida = ClaveProdServ::where('Clave', $atributos_concepto['ClaveProdServ'])->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $atributos_concepto['ClaveUnidad'])->first();
                $descuentoporcentajepartida = 0;
                $multiplicaciondescuentoporcentajepartida  =  $atributos_concepto['Descuento']*100;
                if($multiplicaciondescuentoporcentajepartida > 0){
                  $descuentoporcentajepartida = $multiplicaciondescuentoporcentajepartida/$atributos_concepto['Importe'];
                }
                $iva = '16';
                $filasdetallesxml= $filasdetallesxml.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$atributos_concepto['NoIdentificacion'].'" readonly><b style="font-size:12px;">'.$atributos_concepto['NoIdentificacion'].'</b></td>'.
                    '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($atributos_concepto['Descripcion'], ENT_QUOTES).'</textarea></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$atributos_concepto['Unidad'].'" readonly>'.$atributos_concepto['Unidad'].'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Cantidad']).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($atributos_concepto['Cantidad']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['ValorUnitario']).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($descuentoporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Descuento']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']+$atributos_traslado['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm ordenpartida" name="ordenpartida[]"  readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-md-10">'.
                                '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" readonly><input type="text" class="form-control inputnextdet divorinputmodmd departamentopartida" name="departamentopartida[]" readonly>'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$claveunidadpartida->Nombre.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]"  readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'.
                '</tr>';
                $contadorproductos++;
                $contadorfilas++;   */
            }
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $uuid = $atributos_complemento['UUID'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $data = array(
                "nombrexml" => $nombre_original,
                "comprobante" => $comprobante,
                "array_comprobante" => $array_comprobante,
                "array_emisor" => $array_emisor,
                "array_receptor" => $array_receptor,
                "array_conceptos" => $array_conceptos,
                "TotalImpuestosTrasladados" => $TotalImpuestosTrasladados,
                "array_traslados_impuestos" => $array_traslados_impuestos,
                "uuid" => $uuid,
                "fechatimbrado" => $fechatimbrado ,
                "filasdetallesxml" => $filasdetallesxml
            );
            //eliminar xml de public/xml_cargados
            $eliminarxml = public_path().'/xml_cargados/'.$nombre_original;
            unlink($eliminarxml);
        } else {
            exit('Error al abrir xml.');
        }
        return response()->json($data);
    }
    //obtener proveedores
    public function compras_obtener_proveedores(Request $request){
        if($request->ajax()){
            $data = Proveedor::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc .'\',\''.$data->SolicitarXML .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener almacenes
    public function compras_obtener_almacenes(Request $request){
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
    public function compras_obtener_almacen_por_numero(Request $request){
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
    public function compras_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            switch ($request->tipoalta) {
                case "GASTOS":
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'GASTOS');
                    break;
                case "TOT":
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'TOT');
                    break;
                default:
                    $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', '<>', 'GASTOS')->Where('TipoProd', '<>', 'TOT');
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($numeroalmacen, $tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    public function compras_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        switch ($request->tipoalta) {
            case "GASTOS":
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'GASTOS')->count();
                break;
            case "TOT":
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'TOT')->count();
                break;
            default:
                $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', '<>', 'GASTOS')->Where('TipoProd', '<>', 'TOT')->count();
        }
        if($contarproductos > 0){
            switch ($request->tipoalta) {
                case "GASTOS":
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'GASTOS')->first();
                    break;
                case "TOT":
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'TOT')->first();
                    break;
                default:
                    $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', '<>', 'GASTOS')->Where('TipoProd', '<>', 'TOT')->first();
            }
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
                'NombreClaveProducto' => $producto->NombreClaveProducto,
                'NombreClaveUnidad' => $producto->NombreClaveUnidad,
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
                'NombreClaveProducto' => '',
                'NombreClaveUnidad' => '',
                'CostoDeLista' => '',
                'contarproductos' => $contarproductos
            );
        }
        return response()->json($data);
    }
    //obtener proveedor por numero
    public function compras_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $rfc = '';
        $SolicitarXML = 0;
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
            $plazo = $proveedor->Plazo;
            $rfc = $proveedor->Rfc;
            $SolicitarXML = $proveedor->SolicitarXML;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'plazo' => $plazo,
            'rfc' => $rfc,
            'SolicitarXML' => $SolicitarXML
        );
        return response()->json($data);
    }
    //obtener ordenes de compra por proveedor
    public function compras_obtener_ordenes_compra(Request $request){
        if($request->ajax()){
            switch ($request->tipocompra) {
                case "GASTOS":
                    $data = OrdenCompra::where('Proveedor', $request->numeroproveedor)
                                        ->where('AutorizadoPor', '<>', '')
                                        ->where(function ($query) {
                                            $query->where('Status', 'POR SURTIR')
                                            ->orWhere('Status', 'BACKORDER');
                                        })
                                        ->where(function ($query) {
                                            $query->where('Tipo', 'GASTOS')
                                            ->orWhere('Tipo', 'CAJA CHICA');
                                        })
                                        ->where('Almacen', 0)
                                        ->orderBy('Folio', 'DESC')
                                        ->get();
                    break;
                case "TOT":
                    $data = OrdenCompra::where('Proveedor', $request->numeroproveedor)
                                        ->where('AutorizadoPor', '<>', '')
                                        ->where(function ($query) {
                                            $query->where('Status', 'POR SURTIR')
                                            ->orWhere('Status', 'BACKORDER');
                                        })
                                        ->where('Tipo', 'TOT')
                                        ->orderBy('Folio', 'DESC')
                                        ->get();
                    break;
                default:
                    $data = OrdenCompra::where('Proveedor', $request->numeroproveedor)
                                        ->where('AutorizadoPor', '<>', '')
                                        ->where(function ($query) {
                                            $query->where('Status', 'POR SURTIR')
                                            ->orWhere('Status', 'BACKORDER');
                                        })
                                        ->where('Tipo', '<>', 'GASTOS')
                                        ->where('Tipo', '<>', 'TOT')
                                        ->where('Almacen', '>', 0)
                                        ->orderBy('Folio', 'DESC')
                                        ->get();
            }
            $tipoalta = $request->tipocompra;
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use($tipoalta){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordencompra('.$data->Folio.',\''.$data->Orden .'\',\''.$tipoalta .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener departamentos
    public function compras_obtener_departamentos(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = Departamento::where('Status', 'ALTA')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionardepartamento('.$data->Numero.',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener claves productos
    public function compras_obtener_claves_productos(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveProdServ::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveproducto(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener claves unidades
    public function compras_obtener_claves_unidades(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = ClaveUnidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarclaveunidad(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener datos de la orden de compra seleccionada
    public function compras_obtener_orden_compra(Request $request){
        $ordencompra = OrdenCompra::where('Orden', $request->Orden)->first();
        $almacen = Almacen::where('Numero', $ordencompra->Almacen)->first();
        $tdOrden = '';
        if($request->tipoalta == "PRODUCTOS"){
            if($this->ligarOTaCompra == 'S') $tdOrden = '<td class="tdmod"><input type="text" class="form-control divorinputmodsm" required name="ordenTrabajo[]"</td>';
        }
        //detalles orden compra
        $detallesordencompra = OrdenCompraDetalle::where('Orden', $request->Orden)->get();
        $numerodetallesordencompra = OrdenCompraDetalle::where('Orden', $request->Orden)->count();
        if($numerodetallesordencompra > 0){
            $filasdetallesordencompra = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $arraycodigosdetallesordencompra = array();
            foreach($detallesordencompra as $doc){
                $surtir = Helpers::convertirvalorcorrecto($doc->Surtir);
                if($surtir > 0){
                    $producto = Producto::where('Codigo', $doc->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $producto->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $producto->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    $filasdetallesordencompra= $filasdetallesordencompra.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$doc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$doc->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$doc->Codigo.'</b></td>'.
                        '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($doc->Descripcion, ENT_QUOTES).'</textarea></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$doc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$doc->Unidad.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        $tdOrden.
                        '<td class="tdmod"><input readonly type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->Precio),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" readonly class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->Descuento),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');" >'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" >'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm importedescuentopesospartidaAux"  name="importedescuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->SubTotal),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm trasladoiepspesospartidaAux" name="trasladoiepspesospartidaAux[]" value="0.'.$this->numerocerosconfigurados.'" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm trasladoivapesospartidaAux" name="trasladoivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm retencionivapesospartidaAux" name="retencionivapesospartidaAux[]" value="0.'.$this->numerocerosconfigurados.'" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($doc->Total),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$doc->Orden.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistardepartamentos" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" readonly><input type="text" class="form-control inputnextdet divorinputmodmd departamentopartida" name="departamentopartida[]" readonly>'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'"  readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
                }
                array_push($arraycodigosdetallesordencompra, $doc->Codigo);
            }
        }else{
            $filasdetallesordencompra = '';
        }
        $data = array(
            "ordencompra" => $ordencompra,
            "arraycodigosdetallesordencompra" => $arraycodigosdetallesordencompra,
            "filasdetallesordencompra" => $filasdetallesordencompra,
            "numerodetallesordencompra" => $numerodetallesordencompra,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "almacen" => $almacen,
            "fecha" => Helpers::formatoinputdatetime($ordencompra->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($ordencompra->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($ordencompra->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($ordencompra->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($ordencompra->Iva),
            "total" => Helpers::convertirvalorcorrecto($ordencompra->Total)
        );
        return response()->json($data);
    }
    //guardar compra
    public function compras_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        $uuid=$request->uuid;
        $solicitarxml=$request->solicitarxml;
        $tipo = '';
	    $ExisteUUID = Compra::where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true && $solicitarxml == 1){
	        $Compra = 1;
	    }else{
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofolioserietablamodulos('App\Compra',$request->serie);
            //INGRESAR DATOS A TABLA COMPRAS
            $compra = $folio.'-'.$request->serie;
            $Compra = new Compra;
            $Compra->Compra=$compra;
            $Compra->Serie=$request->serie;
            $Compra->Folio=$folio;
            $Compra->Proveedor=$request->numeroproveedor;
            switch ($request->tipocompra){
                case "TOT":
                    $Compra->Movimiento="TOT";
                    break;
                case "GASTOS";
                    $Compra->Movimiento="GASTOS";
                    break;
                default:
                    $Compra->Movimiento="ALMACEN".$request->numeroalmacen;
                    $tipo = 'productos';
            }
            $Compra->Remision=$request->remision;
            $Compra->Factura=$request->factura;
            if($solicitarxml == 1){
                $Compra->UUID=$request->uuid;
                $Compra->FechaEmitida=Carbon::parse($request->fechaemitida)->toDateTimeString();
                //$Compra->FechaTimbrado=$request->fechatimbrado;
            }else{
                $Compra->UUID="N/A";
                $Compra->FechaEmitida=Helpers::fecha_exacta_accion_datetimestring();
                //$Compra->FechaTimbrado=Helpers::fecha_exacta_accion_datetimestring();
            }
            $Compra->Tipo=$request->tipo;
            $Compra->Plazo=$request->plazo;
            $Compra->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $Compra->Almacen=$request->numeroalmacen;
            $Compra->Orden=$request->orden;
            $Compra->Importe=$request->importe;
            $Compra->Descuento=$request->descuento;
            $Compra->Ieps=$request->ieps;
            $Compra->SubTotal=$request->subtotal;
            $Compra->Iva=$request->iva;
            $Compra->IvaRetencion=$request->retencioniva;
            $Compra->IsrRetencion=$request->retencionisr;
            $Compra->IepsRetencion=$request->retencionieps;
            $Compra->Total=$request->total;
            $Compra->Saldo=$request->total;
            $Compra->Obs=$request->observaciones;
            $Compra->Moneda=$request->moneda;
            $Compra->TipoCambio=$request->pesosmoneda;
            $Compra->EmisorRfc=$request->emisorrfc;
            $Compra->EmisorNombre=$request->emisornombre;
            $Compra->ReceptorRfc=$request->receptorrfc;
            $Compra->ReceptorNombre=$request->receptornombre;
            $Compra->Status="POR PAGAR";
            $Compra->Usuario=Auth::user()->user;
            $Compra->Periodo=$this->periodohoy;
            $Compra->OrdenTrabajo=$request->ordentrabajo;
            $Compra->save();
            //si la alta es POR TOT modificar los totales de la orden de trabajo
            switch ($request->tipo) {
                case "TOT":
                    //obtener total costo y utilidad porque en la compra no se calcula
                    $totalcosto=0;
                    $totalutilidad=0;
                    foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
                        $producto = Producto::where('Codigo', $codigoproductopartida)->first();
                        $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                        //costo total
                        $costototalpartida  = $costopartida * $request->cantidadpartida [$key];
                        //utilidad de la partida
                        $utilidadpartida = $request->subtotalpartida [$key] - Helpers::convertirvalorcorrecto($costototalpartida);
                        $totalcosto = $totalcosto + $costototalpartida;
                        $totalutilidad = $totalutilidad + $utilidadpartida;
                    }
                    $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $request->ordentrabajo)->first();
                    OrdenTrabajo::where('Orden', $request->ordentrabajo)
                                ->update([
                                    'Importe' => $OrdenTrabajoAnterior->Importe + $request->importe,
                                    'Descuento' => $OrdenTrabajoAnterior->Descuento + $request->descuento,
                                    'SubTotal' => $OrdenTrabajoAnterior->SubTotal + $request->subtotal,
                                    'Iva' => $OrdenTrabajoAnterior->Iva + $request->iva,
                                    'Total' => $OrdenTrabajoAnterior->Total + $request->total,
                                    'Costo' => $OrdenTrabajoAnterior->Costo + Helpers::convertirvalorcorrecto($totalcosto),
                                    'Utilidad' => $OrdenTrabajoAnterior->Utilidad + Helpers::convertirvalorcorrecto($totalutilidad)
                                ]);

                    break;
            }
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "COMPRAS";
            $BitacoraDocumento->Movimiento = $compra;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "POR PAGAR";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            $item = 1;
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
                $CompraDetalle=new CompraDetalle;
                $CompraDetalle->Compra = $compra;
                $CompraDetalle->Proveedor = $request->numeroproveedor;
                $CompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CompraDetalle->Codigo = $codigoproductopartida;
                $CompraDetalle->Descripcion = $request->nombreproductopartida [$key];
                $CompraDetalle->Unidad = $request->unidadproductopartida [$key];
                if($tipo == 'productos'){
                    if ($this->ligarOTaCompra == 'S') $CompraDetalle->OT = strtoupper($request->ordenTrabajo[$key]);
                }
                $CompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $CompraDetalle->Precio =  $request->preciopartida [$key];
                $CompraDetalle->Importe = $request->importepartida [$key];
                $CompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $CompraDetalle->Descuento = $request->descuentopesospartida [$key];
                $CompraDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                $CompraDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                $CompraDetalle->SubTotal = $request->subtotalpartida [$key];
                $CompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $CompraDetalle->Iva = $request->trasladoivapesospartida [$key];
                $CompraDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                $CompraDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                $CompraDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                $CompraDetalle->Total = $request->totalpesospartida [$key];
                $CompraDetalle->Orden = $request->ordenpartida [$key];
                $CompraDetalle->Depto = $request->departamentopartida [$key];
                $CompraDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $CompraDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $CompraDetalle->Item = $item;
                $CompraDetalle->save();
                //modificar fechaultimacompra y ultimocosto
                $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
                /*
                $Producto->{'Fecha Ultima Compra'} = Carbon::parse($request->fecha)->toDateTimeString();
                $Producto->{'Ultimo Costo'} = $request->preciopartida [$key];
                $Producto->Costo = $request->preciopartida [$key];
                $Producto->CostoDeLista = $request->preciopartida [$key];
                $Producto->CostoDeVenta = $request->preciopartida [$key];
                $Producto->save();
                */
                if($this->tipodeutilidad == 'Financiera'){
                    //$nuevosubtotalproducto = $request->preciopartida [$key]/(((100 - $Producto->Utilidad) / 100));
                    //$nuevosubtotalproducto = ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] )/(((100 - $Producto->Utilidad) / 100));
                    $restautilidad = Helpers::convertirvalorcorrecto(100) - $Producto->Utilidad;
                    $divisionutilidad = $restautilidad / Helpers::convertirvalorcorrecto(100);
                    $nuevosubtotalproducto = ($request->subtotalpartida [$key] / $request->cantidadpartida [$key]) / $divisionutilidad;
                }else{
                    //$nuevosubtotalproducto = $request->preciopartida [$key]*(1+($Producto->Utilidad/100)); //Ya estaba comentado
                    //$multiplicacionnuevosubtotalproducto = $request->preciopartida [$key]*($Producto->Utilidad/Helpers::convertirvalorcorrecto(100));
                    //$nuevosubtotalproducto = $request->preciopartida [$key]+$multiplicacionnuevosubtotalproducto;
                    //$multiplicacionnuevosubtotalproducto = ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] )*($Producto->Utilidad/Helpers::convertirvalorcorrecto(100));
                    //$nuevosubtotalproducto = ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] )+$multiplicacionnuevosubtotalproducto;
                    $sumautilidad = Helpers::convertirvalorcorrecto(100) + $Producto->Utilidad;
                    $divisionutilidad = $sumautilidad / Helpers::convertirvalorcorrecto(100);
                    $nuevosubtotalproducto = ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] ) * $divisionutilidad;
                }
                $nuevoivaproducto = $nuevosubtotalproducto*($Producto->Impuesto/Helpers::convertirvalorcorrecto(100));
                $nuevototalproducto = $nuevosubtotalproducto + $nuevoivaproducto;
                Producto::where('Codigo', $codigoproductopartida)
                ->update([
                    'Fecha Ultima Compra' => Carbon::parse($request->fecha)->toDateTimeString(),
                    'Costo' => ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] ),
                    'CostoDeLista' => ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] ),
                    'CostoDeVenta' => ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] ),
                    'Ultimo Costo' => ($request->subtotalpartida [$key] / $request->cantidadpartida [$key] ),
                    'SubTotal' => Helpers::convertirvalorcorrecto($nuevosubtotalproducto),
                    'Iva' => Helpers::convertirvalorcorrecto($nuevoivaproducto),
                    'Total' => Helpers::convertirvalorcorrecto($nuevototalproducto)
                ]);
                //modificar proveedor 1 y proveedor 2
                Producto::where('Codigo', $codigoproductopartida)
                ->update([
                    'Proveedor2' => $Producto->Proveedor1,
                    'Proveedor1' => $request->numeroproveedor
                ]);
                //modificar faltante por surtir detalle orden de compra
                $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                $Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                    ->where('Codigo', $codigoproductopartida)
                                    ->update([
                                        'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                    ]);
                //si la alta es por TOT agregar las partidas en la Orden de Trabajo
                switch ($request->tipo) {
                    case "TOT":
                        $producto = Producto::where('Codigo', $codigoproductopartida)->first();
                        $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                        //costo total
                        $costototalpartida  = $costopartida * $request->cantidadpartida [$key];
                        //utilidad de la partida
                        $utilidadpartida = $request->subtotalpartida [$key] - Helpers::convertirvalorcorrecto($costototalpartida);
                        $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordentrabajo)->first();
                        $contardetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordentrabajo)->count();
                        if($contardetallesordentrabajo > 0){
                            $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->ordentrabajo)->orderBy('Partida', 'DESC')->take(1)->get();
                            $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                        }else{
                            $UltimaPartida = 1;
                        }
                        $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                        $OrdenTrabajoDetalle->Orden=$request->ordentrabajo;
                        $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                        $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                        $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                        $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                        $OrdenTrabajoDetalle->Descripcion=$request->nombreproductopartida [$key];
                        $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                        $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                        $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                        $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                        $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                        $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                        $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                        $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                        $OrdenTrabajoDetalle->Iva=$request->trasladoivapesospartida [$key];
                        $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                        $OrdenTrabajoDetalle->Costo=$costopartida;
                        $OrdenTrabajoDetalle->CostoTotal=Helpers::convertirvalorcorrecto($costototalpartida);
                        $OrdenTrabajoDetalle->Utilidad=Helpers::convertirvalorcorrecto($utilidadpartida);
                        $OrdenTrabajoDetalle->Departamento="SERVICIO";
                        $OrdenTrabajoDetalle->Cargo="SERVICIO";
                        $OrdenTrabajoDetalle->Compra=$compra;
                        $OrdenTrabajoDetalle->Item=$item;
                        $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                        $OrdenTrabajoDetalle->Almacen=0;
                        $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                        $OrdenTrabajoDetalle->save();
                        $UltimaPartida++;
                        break;
                    case "GASTOS":
                        break;
                    case "CAJA CHICA":
                        if($request->numeroalmacen > 0){
                            //sumar existencias al almacen
                            $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                            if($ContarExistenciaAlmacen > 0){
                                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                                    Existencia::where('Codigo', $codigoproductopartida)
                                                ->where('Almacen', $request->numeroalmacen)
                                                ->update([
                                                    'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                                ]);
                            }else{
                                    $ExistenciaAlmacen = new Existencia;
                                    $ExistenciaAlmacen->Codigo = $codigoproductopartida;
                                    $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                                    $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                                    $ExistenciaAlmacen->save();
                            }
                        }
                        break;
                    default:
                        //sumar existencias al almacen
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                                $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                                Existencia::where('Codigo', $codigoproductopartida)
                                            ->where('Almacen', $request->numeroalmacen)
                                            ->update([
                                                'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                            ]);
                        }else{
                                $ExistenciaAlmacen = new Existencia;
                                $ExistenciaAlmacen->Codigo = $codigoproductopartida;
                                $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                                $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                                $ExistenciaAlmacen->save();
                        }
                }
                $item++;
            }
            //modificar el status de la orden de compra a SURTIDO o BACKORDER
            $detallesordenporsurtir = OrdenCompraDetalle::where('Orden', $request->orden)->where('Surtir', '>', 0)->count();
            if($detallesordenporsurtir > 0){
                OrdenCompra::where('Orden', $request->orden)
                                    ->update([
                                        'Status' => "BACKORDER"
                                    ]);
            }else{
                OrdenCompra::where('Orden', $request->orden)
                                    ->update([
                                        'Status' => "SURTIDO"
                                    ]);
            }
        }
            return response()->json($Compra);
    }
    //obtener compra a modificar
    public function compras_obtener_compra(Request $request){
        $compra = Compra::where('Compra', $request->compramodificar)->first();
        $almacen = Almacen::where('Numero', $compra->Almacen)->first();
        $proveedor = Proveedor::where('Numero', $compra->Proveedor)->first();
        $filaOT = '';
        //detalles orden compra
        $detallescompra = CompraDetalle::where('Compra', $request->compramodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallescompra = CompraDetalle::where('Compra', $request->compramodificar)->count();
        $filasdetallescompra = '';
        if($numerodetallescompra > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallescompra as $dc){
                    $producto = Producto::where('Codigo', $dc->Codigo)->first();
                    $Existencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', $compra->Almacen)->first();
                    $parsleymax = $dc->Cantidad;
                    $cantidadpartidadetalleordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->where('Codigo', $dc->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $dc->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $dc->ClaveUnidad)->first();
                    $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                    $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                    $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                    $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                    $condepartamento = Departamento::where('Numero', $dc->Depto)->count();
                    $numerodepartamento = "";
                    $nombredepartamento = "";
                    if($condepartamento > 0){
                        $departamento = Departamento::where('Numero', $dc->Depto)->first();
                        $numerodepartamento = $departamento->Numero;
                        $nombredepartamento = $departamento->Nombre;
                    }
                    //importante porque si se quiere hacer una divison con 0 marca ERROR
                    $porcentajeieps = 0;
                    $porcentajeretencioniva = 0;
                    $porcentajeretencionisr = 0;
                    $porcentajeretencionieps = 0;
                    if($dc->Ieps > 0){
                        $porcentajeieps = ($dc->Ieps * 100) / $dc->ImporteDescuento;
                    }
                    if($dc->IvaRetencion > 0){
                        $porcentajeretencioniva = ($dc->IvaRetencion * 100) / $dc->SubTotal;
                    }
                    if($dc->IsrRetencion > 0){
                        $porcentajeretencionisr = ($dc->IsrRetencion * 100) / $dc->SubTotal;
                    }
                    if($dc->IepsRetencion > 0){
                        $porcentajeretencionieps = ($dc->IepsRetencion * 100) / $dc->SubTotal;
                    }
                    if($compra->Movimiento != "GASTOS" && $compra->Movimiento != "TOT"){
                        if($this->ligarOTaCompra == 'S'){
                            $filaOT = '<td class="tdmod"><input readonly type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" value="'.$dc->OT.'"></td>';
                        }
                    }
                    $filasdetallescompra= $filasdetallescompra.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$dc->Codigo.'</b></td>'.
                        '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet nombreproductopartida" name="nombreproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'</textarea></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        $filaOT.
                        '<td class="tdmod">'.
                            '<input type="hidden" class="form-control cantidadinicialpartida" name="cantidadinicialpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" >'.
                            '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                            '<input type="hidden" class="form-control operacionaritmetica" name="operacionaritmetica[]" >'.
                            '<input type="hidden" class="form-control cantidadoperacionaritmetica" name="cantidadoperacionaritmetica[]" >'.
                            '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '<input type="number" readonly step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.',\''.$tipo.'\');">'.
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Precio),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" >'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" readonly class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Descuento), $this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" >'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" >'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm importedescuentopesospartidaAux" name="importedescuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->ImporteDescuento),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm trasladoivapesospartidaAux" name="trasladoivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"'.
                            '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Total),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$dc->Orden.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistardepartamentos" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.'</div>'.'<div class="col-md-10">'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" value="'.$numerodepartamento.'" readonly><input type="text" class="form-control inputnextdet divorinputmodmd departamentopartida" name="departamentopartida[]" value="'.$nombredepartamento.'" readonly>'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->PrecioMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->DescuentoMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly data-parsley-length="[1, 20]">'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproducto.'" readonly></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly data-parsley-length="[1, 5]">'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'"  readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
            }
            $arraycodigosdetallesordencompra = array();
            $detallesordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->get();
            foreach($detallesordencompra as $doc){
                array_push($arraycodigosdetallesordencompra, $doc->Codigo);
            }
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($compra->Status == 'LIQUIDADA' || $compra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            } else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($compra->Status == 'LIQUIDADA' || $compra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($compra->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "compra" => $compra,
            "arraycodigosdetallesordencompra" => $arraycodigosdetallesordencompra,
            "filasdetallescompra" => $filasdetallescompra,
            "numerodetallescompra" => $numerodetallescompra,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "almacen" => $almacen,
            "proveedor" => $proveedor,
            "fecha" => Helpers::formatoinputdatetime($compra->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($compra->Fecha),
            "fechaemitida" => Helpers::formatoinputdatetime($compra->FechaEmitida),
            "fechatimbrado" => Helpers::formatoinputdatetime($compra->FechaTimbrado),
            "importe" => Helpers::convertirvalorcorrecto($compra->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($compra->Descuento),
            "ieps" => Helpers::convertirvalorcorrecto($compra->Ieps),
            "subtotal" => Helpers::convertirvalorcorrecto($compra->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($compra->Iva),
            "ivaretencion" => Helpers::convertirvalorcorrecto($compra->IvaRetencion),
            "isrretencion" => Helpers::convertirvalorcorrecto($compra->IsrRetencion),
            "iepsretencion" => Helpers::convertirvalorcorrecto($compra->IepsRetencion),
            "total" => Helpers::convertirvalorcorrecto($compra->Total),
            "tipocambio" => Helpers::convertirvalorcorrecto($compra->TipoCambio),
            "movimiento" => $compra->Movimiento,
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //evaluar existencias en almacen
    public function compras_obtener_existencias_partida(Request $request){
        $compra = Compra::where('Compra', $request->compra)->first();
        switch ($compra->Tipo){
            case "TOT":
                $nuevaexistencia = $request->cantidadpartida;
                break;
            case "GASTOS":
                $nuevaexistencia = $request->cantidadpartida;
                break;
            case "CAJA CHICA":
                if($compra->Almacen > 0){
                    $existencias = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
                    $compra = $request->folio.'-'.$request->serie;
                    $detallecompra = CompraDetalle::where('Compra', $compra)->where('Codigo', $request->codigopartida)->count();
                    $nuevaexistencia = 0;
                    if($detallecompra > 0){
                        $detallecompra = CompraDetalle::where('Compra', $compra)->where('Codigo', $request->codigopartida)->first();
                        $nuevaexistencia = $existencias->Existencias + $detallecompra->Cantidad;
                    }else{
                        $nuevaexistencia = $existencias->Existencias;
                    }
                }else{
                    $nuevaexistencia = $request->cantidadpartida;
                }
                break;
            default:
                $existencias = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
                $compra = $request->folio.'-'.$request->serie;
                $detallecompra = CompraDetalle::where('Compra', $compra)->where('Codigo', $request->codigopartida)->count();
                $nuevaexistencia = 0;
                if($detallecompra > 0){
                    $detallecompra = CompraDetalle::where('Compra', $compra)->where('Codigo', $request->codigopartida)->first();
                    $nuevaexistencia = $existencias->Existencias + $detallecompra->Cantidad;
                }else{
                    $nuevaexistencia = $existencias->Existencias;
                }
        }
        return response()->json(Helpers::convertirvalorcorrecto($nuevaexistencia));
    }
    //obtener existencias
    public function compras_obtener_existencias_almacen(Request $request){
        $ContarExistencia = Existencia::where('Codigo', $request->codigo)->where('Almacen', $request->numeroalmacen)->count();
        if($ContarExistencia > 0){
            $Existencia = Existencia::where('Codigo', $request->codigo)->where('Almacen',$request->numeroalmacen)->first();
            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
        }else{
            $Existencias = Helpers::convertirvalorcorrecto(0);
        }
        return response()->json($Existencias);
    }
    //obtener valor moduficacion permitida
    public function compras_obtener_valor_modificacionpermitida(Request $request){
        $compra = Compra::where('Compra', $request->compra)->first();
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($compra->Status == 'LIQUIDADA' || $compra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            } else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($compra->Status == 'LIQUIDADA' || $compra->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($compra->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        return response()->json($modificacionpermitida);
    }
    //guardar modificacion compra
    public function compras_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $uuid=$request->uuid;
        $compra = $request->compra;
	    $ExisteUUID = Compra::where('Compra', '<>', $compra)->where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true && $uuid != "N/A"){
	        $Compra = 1;
	    }else{
            $Compra = Compra::where('Compra', $compra)->first();
            //validar si las partidas en las modiifcacion son las mismas que los detalles de los traspasos
            // si no son las mismas comparar y eliminar las partidas que corresponden en la tabla detalles de OrdenesTrabajo y Traspasos
            //array partidas antes de modificacion
            $ArrayDetallesCompraAnterior = Array();
            $DetallesCompraAnterior = CompraDetalle::where('Compra', $compra)->get();
            foreach($DetallesCompraAnterior as $detalle){
                //array_push($ArrayDetallesCompraAnterior, $detalle->Codigo);
                array_push($ArrayDetallesCompraAnterior, $detalle->Compra.'#'.$detalle->Codigo.'#'.$detalle->Item);
            }
            //array partida despues de modificacion
            $ArrayDetallesCompraNuevo = Array();
            foreach ($request->codigoproductopartida as $key => $nuevocodigo){
                //array_push($ArrayDetallesCompraNuevo, $nuevocodigo);
                if($request->agregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesCompraNuevo, $compra.'#'.$nuevocodigo.'#'.$request->itempartida [$key]);
                }
            }
            //diferencias entre arreglos
            $diferencias_arreglos = array_diff($ArrayDetallesCompraAnterior, $ArrayDetallesCompraNuevo);
            //iteramos las diferencias entre arreglos
            if(count($diferencias_arreglos) > 0){
                foreach($diferencias_arreglos as $eliminapartida){
                    $explode_d = explode("#",$eliminapartida);
                    $detallecompra = CompraDetalle::where('Compra', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->first();
                    switch ($Compra->Tipo){
                        case "TOT":
                            //si es TOT restar del total de la orden de trabajo los totales de la partida de la compra y eliminar la partida de la orden de trabajo
                            //obtener total costo y utilidad porque en la compra no se calcula
                            $detallescompra = CompraDetalle::where('Compra', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->get();
                            foreach ($detallescompra as $detalle){
                                $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                                $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                                //costo total
                                $costototalpartida  = $costopartida * $detalle->Cantidad;
                                //utilidad de la partida
                                $utilidadpartida = $detalle->SubTotal - Helpers::convertirvalorcorrecto($costototalpartida);
                                $CompraAnterior = Compra::where('Compra', $explode_d[0])->first();
                                $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)->first();
                                OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)
                                            ->update([
                                                'Importe' => $OrdenTrabajoAnterior->Importe - $detalle->Importe,
                                                'Descuento' => $OrdenTrabajoAnterior->Descuento - $detalle->Descuento,
                                                'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $detalle->SubTotal,
                                                'Iva' => $OrdenTrabajoAnterior->Iva - $detalle->Iva,
                                                'Total' => $OrdenTrabajoAnterior->Total - $detalle->Total,
                                                'Costo' => $OrdenTrabajoAnterior->Costo - Helpers::convertirvalorcorrecto($costototalpartida),
                                                'Utilidad' => $OrdenTrabajoAnterior->Utilidad - Helpers::convertirvalorcorrecto($utilidadpartida)
                                            ]);
                            }
                            break;
                        case "GASTOS":
                            break;
                        case "CAJA CHICA":
                            if($request->numeroalmacen > 0){
                                //restar existencias a almacen principal
                                $RestarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                                $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $detallecompra->Cantidad;
                                Existencia::where('Codigo', $explode_d[1])
                                            ->where('Almacen', $request->numeroalmacen)
                                            ->update([
                                                'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                            ]);
                            }
                            break;
                        default:
                            //restar existencias a almacen principal
                            $RestarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                            $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $detallecompra->Cantidad;
                            Existencia::where('Codigo', $explode_d[1])
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                        ]);
                    }
                    //modificar faltante por surtir detalle orden de compra
                    $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $detallecompra->Orden)->where('Codigo', $explode_d[1])->first();
                    $Surtir = $OrdenCompraDetalle->Surtir+$detallecompra->Cantidad  [$key];
                    OrdenCompraDetalle::where('Orden', $detallecompra->Orden)
                                        ->where('Codigo', $explode_d[1])
                                        ->update([
                                            'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                        ]);
                    //eliminar detalle de la compra eliminado
                    $eliminardetallecompra= CompraDetalle::where('Compra', $explode_d[0])->where('Codigo', $explode_d[1])->where('Item', $explode_d[2])->forceDelete();
                }
            }
            //modificar compra
            switch ($request->tipo){
                case "TOT":
                    $movimiento="TOT";
                    break;
                case "GASTOS";
                    $movimiento="GASTOS";
                    break;
                case "CAJA CHICA";
                    if($request->numeroalmacen > 0){
                        $movimiento="ALMACEN".$request->numeroalmacen;
                    }else{
                        $movimiento="GASTOS";
                    }
                    break;
                default:
                    $movimiento="ALMACEN".$request->numeroalmacen;
            }
            Compra::where('Compra', $compra)
            ->update([
                'Proveedor'=>$request->numeroproveedor,
                'Movimiento'=>$movimiento,
                'Remision'=>$request->remision,
                'Factura'=>$request->factura,
                'UUID'=>$request->uuid,
                'Tipo'=>$request->tipo,
                'Plazo'=>$request->plazo,
                'Fecha'=>Carbon::parse($request->fecha)->toDateTimeString(),
                'Almacen'=>$request->numeroalmacen,
                'Importe'=>$request->importe,
                'Descuento'=>$request->descuento,
                'Ieps'=>$request->ieps,
                'SubTotal'=>$request->subtotal,
                'Iva'=>$request->iva,
                'IvaRetencion'=>$request->retencioniva,
                'IsrRetencion'=>$request->retencionisr,
                'IepsRetencion'=>$request->retencionieps,
                'Total'=>$request->total,
                'Saldo'=>$request->total,
                'Obs'=>$request->observaciones,
                'Moneda'=>$request->moneda,
                'TipoCambio'=>$request->pesosmoneda,
                'FechaEmitida'=>Carbon::parse($request->fechaemitida)->toDateTimeString(),
                'FechaTimbrado'=>Carbon::parse($request->fechatimbrado)->toDateTimeString(),
                'EmisorRfc'=>$request->emisorrfc,
                'EmisorNombre'=>$request->emisornombre,
                'ReceptorRfc'=>$request->receptorrfc,
                'ReceptorNombre'=>$request->receptornombre
            ]);
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "COMPRAS";
            $BitacoraDocumento->Movimiento = $compra;
            $BitacoraDocumento->Aplicacion = "CAMBIO";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = $Compra->Status;
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
                //if la partida se agrego en la modificacion se agrega en los detalles
                if($request->agregadoen [$key] == 'modificacion'){
                    $contaritems = CompraDetalle::select('Item')->where('Compra', $compra)->count();
                    if($contaritems > 0){
                        $item = CompraDetalle::select('Item')->where('Compra', $compra)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
                    $CompraDetalle=new CompraDetalle;
                    $CompraDetalle->Compra = $compra;
                    $CompraDetalle->Proveedor = $request->numeroproveedor;
                    $CompraDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                    $CompraDetalle->Codigo = $codigoproductopartida;
                    $CompraDetalle->Descripcion = $request->nombreproductopartida [$key];
                    $CompraDetalle->Unidad = $request->unidadproductopartida [$key];
                    $CompraDetalle->Cantidad =  $request->cantidadpartida  [$key];
                    $CompraDetalle->Precio =  $request->preciopartida [$key];
                    $CompraDetalle->Importe = $request->importepartida [$key];
                    $CompraDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                    $CompraDetalle->Descuento = $request->descuentopesospartida [$key];
                    $CompraDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                    $CompraDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                    $CompraDetalle->SubTotal = $request->subtotalpartida [$key];
                    $CompraDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                    $CompraDetalle->Iva = $request->trasladoivapesospartida [$key];
                    $CompraDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                    $CompraDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                    $CompraDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                    $CompraDetalle->Total = $request->totalpesospartida [$key];
                    $CompraDetalle->Orden = $request->ordenpartida [$key];
                    $CompraDetalle->Depto = $request->departamentopartida [$key];
                    $CompraDetalle->ClaveProducto = $request->claveproductopartida [$key];
                    $CompraDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                    $CompraDetalle->Item = $ultimoitem;
                    $CompraDetalle->save();
                    //modificar fechaultimacompra y ultimocosto
                    $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
                    Producto::where('Codigo', $codigoproductopartida)
                    ->update([
                        'Fecha Ultima Compra' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Ultimo Costo' => $request->preciopartida [$key]
                    ]);
                    /*
                    $Producto->{'Fecha Ultima Compra'} = Carbon::parse($request->fecha)->toDateTimeString();
                    $Producto->{'Ultimo Costo'} = $request->preciopartida [$key];
                    $Producto->save();
                    */
                    //modificar faltante por surtir detalle orden de compra
                    $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                    $Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                    OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                        ->where('Codigo', $codigoproductopartida)
                                        ->update([
                                            'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                        ]);
                    //si la compra es por TOT agregar las partidas en la Orden de Trabajo
                    switch ($request->tipo) {
                        case "TOT":
                            $producto = Producto::where('Codigo', $codigoproductopartida)->first();
                            $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                            //costo total
                            $costototalpartida  = $costopartida * $request->cantidadpartida [$key];
                            //utilidad de la partida
                            $utilidadpartida = $request->subtotalpartida [$key] - Helpers::convertirvalorcorrecto($costototalpartida);
                            $OrdenTrabajo = OrdenTrabajo::where('Orden', $request->ordentrabajo)->first();
                            $contardetallesordentrabajo = OrdenTrabajoDetalle::where('Orden', $request->ordentrabajo)->count();
                            if($contardetallesordentrabajo > 0){
                                $UltimaPartidaOrdenTrabajoDetalle = OrdenTrabajoDetalle::select('Partida')->where('Orden', $request->ordentrabajo)->orderBy('Partida', 'DESC')->take(1)->get();
                                $UltimaPartida = $UltimaPartidaOrdenTrabajoDetalle[0]->Partida+1;
                            }else{
                                $UltimaPartida = 1;
                            }
                            $OrdenTrabajoDetalle = new OrdenTrabajoDetalle;
                            $OrdenTrabajoDetalle->Orden=$request->ordentrabajo;
                            $OrdenTrabajoDetalle->Cliente=$OrdenTrabajo->Cliente;
                            $OrdenTrabajoDetalle->Agente=$OrdenTrabajo->Agente;
                            $OrdenTrabajoDetalle->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
                            $OrdenTrabajoDetalle->Codigo=$codigoproductopartida;
                            $OrdenTrabajoDetalle->Descripcion=$request->nombreproductopartida [$key];
                            $OrdenTrabajoDetalle->Unidad=$request->unidadproductopartida [$key];
                            $OrdenTrabajoDetalle->Cantidad=$request->cantidadpartida [$key];
                            $OrdenTrabajoDetalle->Precio=$request->preciopartida [$key];
                            $OrdenTrabajoDetalle->Importe=$request->importepartida [$key];
                            $OrdenTrabajoDetalle->Dcto=$request->descuentoporcentajepartida [$key];
                            $OrdenTrabajoDetalle->Descuento= $request->descuentopesospartida  [$key];
                            $OrdenTrabajoDetalle->SubTotal=$request->subtotalpartida [$key];
                            $OrdenTrabajoDetalle->Impuesto=$request->ivaporcentajepartida [$key];
                            $OrdenTrabajoDetalle->Iva=$request->trasladoivapesospartida [$key];
                            $OrdenTrabajoDetalle->Total=$request->totalpesospartida [$key];
                            $OrdenTrabajoDetalle->Costo=$costopartida;
                            $OrdenTrabajoDetalle->CostoTotal=Helpers::convertirvalorcorrecto($costototalpartida);
                            $OrdenTrabajoDetalle->Utilidad=Helpers::convertirvalorcorrecto($utilidadpartida);
                            $OrdenTrabajoDetalle->Departamento="SERVICIO";
                            $OrdenTrabajoDetalle->Cargo="SERVICIO";
                            $OrdenTrabajoDetalle->Compra=$compra;
                            $OrdenTrabajoDetalle->Item=$ultimoitem;
                            $OrdenTrabajoDetalle->Usuario=Auth::user()->user;
                            $OrdenTrabajoDetalle->Almacen=0;
                            $OrdenTrabajoDetalle->Partida=$UltimaPartida;
                            $OrdenTrabajoDetalle->save();
                            $UltimaPartida++;
                            //sumar totales partida agregada compra a orden trabajo
                            $producto = Producto::where('Codigo', $codigoproductopartida)->first();
                            $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                            //costo total
                            $costototalpartida  = $costopartida * $request->cantidadpartida [$key];
                            //utilidad de la partida
                            $utilidadpartida = $request->subtotalpartida [$key] - Helpers::convertirvalorcorrecto($costototalpartida);
                            $CompraAnterior = Compra::where('Compra', $compra)->first();
                            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)->first();
                            OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)
                                        ->update([
                                                'Importe' => $OrdenTrabajoAnterior->Importe + $request->importepartida [$key],
                                                'Descuento' => $OrdenTrabajoAnterior->Descuento + $request->descuentopesospartida  [$key],
                                                'SubTotal' => $OrdenTrabajoAnterior->SubTotal + $request->subtotalpartida [$key],
                                                'Iva' => $OrdenTrabajoAnterior->Iva + $request->trasladoivapesospartida [$key],
                                                'Total' => $OrdenTrabajoAnterior->Total + $request->totalpesospartida [$key],
                                                'Costo' => $OrdenTrabajoAnterior->Costo + Helpers::convertirvalorcorrecto($costototalpartida),
                                                'Utilidad' => $OrdenTrabajoAnterior->Utilidad + Helpers::convertirvalorcorrecto($utilidadpartida)
                                        ]);
                            break;
                        case "GASTOS":
                            break;
                        case "CAJA CHICA":
                            if($request->numeroalmacen > 0){
                                //sumar existencias al almacen
                                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                                if($ContarExistenciaAlmacen > 0){
                                        $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                                        Existencia::where('Codigo', $codigoproductopartida)
                                                    ->where('Almacen', $request->numeroalmacen)
                                                    ->update([
                                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                                    ]);
                                }else{
                                        $ExistenciaAlmacen = new Existencia;
                                        $ExistenciaAlmacen->Codigo = $codigoproductopartida;
                                        $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                                        $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                                        $ExistenciaAlmacen->save();
                                }
                            }
                            break;
                        default:
                            //sumar existencias al almacen
                            $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                            if($ContarExistenciaAlmacen > 0){
                                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                                    Existencia::where('Codigo', $codigoproductopartida)
                                                ->where('Almacen', $request->numeroalmacen)
                                                ->update([
                                                    'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                                ]);
                            }else{
                                    $ExistenciaAlmacen = new Existencia;
                                    $ExistenciaAlmacen->Codigo = $codigoproductopartida;
                                    $ExistenciaAlmacen->Almacen = $request->numeroalmacen;
                                    $ExistenciaAlmacen->Existencias = $request->cantidadpartida [$key];
                                    $ExistenciaAlmacen->save();
                            }
                    }
                    $ultimoitem++;
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar faltante por surtir detalle orden de compra
                    //sumar
                    $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                    $Surtir = $OrdenCompraDetalle->Surtir+$request->cantidadpartidadb  [$key];
                    OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                        ->where('Codigo', $codigoproductopartida)
                                        ->update([
                                            'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                        ]);
                    //restar
                    $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                    $Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                    OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                        ->where('Codigo', $codigoproductopartida)
                                        ->update([
                                            'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                        ]);
                    //si la compra es por TOT agregar las partidas en la Orden de Trabajo
                    switch ($request->tipo) {
                        case "TOT":
                            //restar totales partida db compra a orden trabajo
                            //si es TOT restar del total de la orden de trabajo los totales de la partida de la compra y eliminar la partida de la orden de trabajo
                            //obtener total costo y utilidad porque en la compra no se calcula
                            $detalle = CompraDetalle::where('Compra', $compra)->where('Codigo', $codigoproductopartida)->where('Item', $request->itempartida [$key])->first();
                            $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                            $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                            //costo total
                            $costototalpartida  = $costopartida * $detalle->Cantidad;
                            //utilidad de la partida
                            $utilidadpartida = $detalle->SubTotal - Helpers::convertirvalorcorrecto($costototalpartida);
                            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $request->ordentrabajo)->first();
                            OrdenTrabajo::where('Orden', $request->ordentrabajo)
                                        ->update([
                                                'Importe' => $OrdenTrabajoAnterior->Importe - $detalle->Importe,
                                                'Descuento' => $OrdenTrabajoAnterior->Descuento - $detalle->Descuento,
                                                'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $detalle->SubTotal,
                                                'Iva' => $OrdenTrabajoAnterior->Iva - $detalle->Iva,
                                                'Total' => $OrdenTrabajoAnterior->Total - $detalle->Total,
                                                'Costo' => $OrdenTrabajoAnterior->Costo - Helpers::convertirvalorcorrecto($costototalpartida),
                                                'Utilidad' => $OrdenTrabajoAnterior->Utilidad - Helpers::convertirvalorcorrecto($utilidadpartida)
                                        ]);
                            //sumar totales partida modificada compra a orden trabajo
                            $producto = Producto::where('Codigo', $codigoproductopartida)->first();
                            $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                            //costo total
                            $costototalpartida  = $costopartida * $request->cantidadpartida [$key];
                            //utilidad de la partida
                            $utilidadpartida = $request->subtotalpartida [$key] - Helpers::convertirvalorcorrecto($costototalpartida);
                            $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $request->ordentrabajo)->first();
                            OrdenTrabajo::where('Orden', $request->ordentrabajo)
                                        ->update([
                                                'Importe' => $OrdenTrabajoAnterior->Importe + $request->importepartida [$key],
                                                'Descuento' => $OrdenTrabajoAnterior->Descuento + $request->descuentopesospartida  [$key],
                                                'SubTotal' => $OrdenTrabajoAnterior->SubTotal + $request->subtotalpartida [$key],
                                                'Iva' => $OrdenTrabajoAnterior->Iva + $request->trasladoivapesospartida [$key],
                                                'Total' => $OrdenTrabajoAnterior->Total + $request->totalpesospartida [$key],
                                                'Costo' => $OrdenTrabajoAnterior->Costo + Helpers::convertirvalorcorrecto($costototalpartida),
                                                'Utilidad' => $OrdenTrabajoAnterior->Utilidad + Helpers::convertirvalorcorrecto($utilidadpartida)
                                        ]);
                            //modificar detalle orden trabajo
                            OrdenTrabajoDetalle::where('Orden', $request->ordentrabajo)->where('Compra', $compra)->where('Codigo', $codigoproductopartida)
                                        ->update([
                                            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
                                            'Codigo' => $codigoproductopartida,
                                            'Descripcion' => $request->nombreproductopartida [$key],
                                            'Unidad' => $request->unidadproductopartida [$key],
                                            'Cantidad' => $request->cantidadpartida [$key],
                                            'Precio' => $request->preciopartida [$key],
                                            'Importe' => $request->importepartida [$key],
                                            'Dcto' => $request->descuentoporcentajepartida [$key],
                                            'Descuento' => $request->descuentopesospartida  [$key],
                                            'SubTotal' => $request->subtotalpartida [$key],
                                            'Impuesto' => $request->ivaporcentajepartida [$key],
                                            'Iva' => $request->trasladoivapesospartida [$key],
                                            'Total' => $request->totalpesospartida [$key],
                                            'Costo' => $costopartida,
                                            'CostoTotal' => Helpers::convertirvalorcorrecto($costototalpartida),
                                            'Utilidad' => Helpers::convertirvalorcorrecto($utilidadpartida)
                                        ]);
                            break;
                        case "GASTOS":
                            break;
                        case "CAJA CHICA":
                            if($request->numeroalmacen > 0){
                                //restar existencias del almacen
                                $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                                if($ContarExistenciaAlmacen > 0){
                                    $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartidadb [$key];
                                    Existencia::where('Codigo', $codigoproductopartida)
                                                ->where('Almacen', $request->numeroalmacen)
                                                ->update([
                                                    'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                                ]);
                                }
                                //sumar existencias a almacen principal
                                $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                                Existencia::where('Codigo', $codigoproductopartida)
                                            ->where('Almacen', $request->numeroalmacen)
                                            ->update([
                                                'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                                            ]);
                            }
                            break;
                        default:
                            //restar existencias del almacen
                            $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                            if($ContarExistenciaAlmacen > 0){
                                $ExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                                $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias - $request->cantidadpartidadb [$key];
                                Existencia::where('Codigo', $codigoproductopartida)
                                            ->where('Almacen', $request->numeroalmacen)
                                            ->update([
                                                'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                            ]);
                            }
                            //sumar existencias a almacen principal
                            $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                            $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartida [$key];
                            Existencia::where('Codigo', $codigoproductopartida)
                                        ->where('Almacen', $request->numeroalmacen)
                                        ->update([
                                            'Existencias' => Helpers::convertirvalorcorrecto($SumarExistenciaNuevaAlmacen)
                                        ]);
                    }
                    //modificar detalle importante no mover de aqui
                    CompraDetalle::where('Compra', $compra)
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
                        'ImporteDescuento' => $request->importedescuentopesospartida [$key],
                        'Ieps' => $request->trasladoiepspesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->trasladoivapesospartida [$key],
                        'IvaRetencion' => $request->retencionivapesospartida [$key],
                        'IsrRetencion' => $request->retencionisrpesospartida [$key],
                        'IepsRetencion' => $request->retencioniepspesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Depto' => $request->departamentopartida [$key],
                        'ClaveProducto' => $request->claveproductopartida [$key],
                        'ClaveUnidad' => $request->claveunidadpartida [$key]
                    ]);
                }
            }
            //modificar el status de la orden de compra a SURTIDO o BACKORDER
            $detallesordenporsurtir = OrdenCompraDetalle::where('Orden', $Compra->Orden)->where('Surtir', '>', Helpers::convertirvalorcorrecto(0))->count();
            if($detallesordenporsurtir > 0){
                OrdenCompra::where('Orden', $Compra->Orden)
                            ->update([
                                'Status' => "BACKORDER"
                            ]);
            }else{
                OrdenCompra::where('Orden', $Compra->Orden)
                            ->update([
                                'Status' => "SURTIDO"
                            ]);
            }
        }
        return response()->json($Compra);
    }
    //verificar si la compra tiene relacion con alguna cuenta por pagar
    public function compras_verificar_uso_en_modulos(Request $request){
        $Compra = Compra::where('Compra', $request->compradesactivar)->first();
        $numerocuentasporpagar = CuentaXPagarDetalle::where('Compra', $request->compradesactivar)->where('Abono', '>', 0)->count();
        $numerocontrareciboscompra = ContraReciboDetalle::where('Compra', $request->compradesactivar)->where('Total', '>', 0)->count();
        $numeronotasproveedor = NotaProveedorDocumento::where('Compra', $request->compradesactivar)->where('Descuento', '>', 0)->count();
        $numerocuentaxpagar = 0;
        $numerocontrarecibo = 0;
        $numeronotaproveedor = 0;
        $numerodetallesconexistenciasinsuficientes = 0;
        //verificar si hay un contrarecibo ligado
        if($numerocontrareciboscompra > 0){
            $detallecontrarecibo = ContraReciboDetalle::where('Compra', $request->compradesactivar)->first();
            $numerocontrarecibo = $detallecontrarecibo->ContraRecibo;
        }
        //verificar si hay una cuenta por pagar ligada
        if($numerocuentasporpagar > 0){
            $detallecuentaxpagar = CuentaXPagarDetalle::where('Compra', $request->compradesactivar)->first();
            $numerocuentaxpagar = $detallecuentaxpagar->Pago;
        }
        //verificar si hay una nota proveedor ligada
        if($numeronotasproveedor > 0){
            $detallenotaproveedor = NotaProveedorDocumento::where('Compra', $request->compradesactivar)->first();
            $numeronotaproveedor = $detallenotaproveedor->Nota;
        }
        //verificar si el almacen cuenta con las existencias
        $comprabaja = Compra::where('Compra', $request->compradesactivar)->first();
        $detallescomprabaja = CompraDetalle::where('Compra', $request->compradesactivar)->get();
        foreach($detallescomprabaja as $detallecomprabaja){
            switch ($comprabaja->Tipo){
                case "TOT":
                    break;
                case "GASTOS":
                    break;
                case "CAJA CHICA":
                    if($comprabaja->Almacen > 0){
                        $ContarExistenciaAlmacen = Existencia::where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->count();
                        if($ContarExistenciaAlmacen > 0){
                            $existencias = Existencia::select('Existencias')->where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->first();
                            $existenciascodigo = $existencias->Existencias;
                        }else{
                            $existenciascodigo = 0;
                        }
                        if($detallecomprabaja->Cantidad > $existenciascodigo){
                            $numerodetallesconexistenciasinsuficientes++;
                        }
                    }
                    break;
                default:
                    $ContarExistenciaAlmacen = Existencia::where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->count();
                    if($ContarExistenciaAlmacen > 0){
                        $existencias = Existencia::select('Existencias')->where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->first();
                        $existenciascodigo = $existencias->Existencias;
                    }else{
                        $existenciascodigo = 0;
                    }
                    if($detallecomprabaja->Cantidad > $existenciascodigo){
                        $numerodetallesconexistenciasinsuficientes++;
                    }
            }

        }
        $resultadofechas = Helpers::compararanoymesfechas($Compra->Fecha);
        $data = array (
            'numerocuentasporpagar' => $numerocuentasporpagar,
            'numerocuentaxpagar' => $numerocuentaxpagar,
            'numerocontrareciboscompra' => $numerocontrareciboscompra,
            'numerocontrarecibo' => $numerocontrarecibo,
            'numeronotasproveedor' => $numeronotasproveedor,
            'numeronotaproveedor' => $numeronotaproveedor,
            'numerodetallesconexistenciasinsuficientes' => $numerodetallesconexistenciasinsuficientes,
            'resultadofechas' => $resultadofechas,
            'Status' => $Compra->Status
        );
        return response()->json($data);
    }
    //dar de baja compra
    public function compras_alta_o_baja(Request $request){
        $Compra = Compra::where('Compra', $request->compradesactivar)->first();
        //si la compra es POR TOT modificar los totales de la orden de trabajo
        switch ($Compra->Tipo) {
            case "TOT":
                //obtener total costo y utilidad porque en la compra no se calcula
                $totalcosto=0;
                $totalutilidad=0;
                $detallescompra = CompraDetalle::where('Compra', $request->compradesactivar)->get();
                foreach ($detallescompra as $detalle){
                    $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                    $costopartida = Helpers::convertirvalorcorrecto($producto->Costo);
                    //costo total
                    $costototalpartida  = $costopartida * $detalle->Cantidad;
                    //utilidad de la partida
                    $utilidadpartida = $detalle->SubTotal - Helpers::convertirvalorcorrecto($costototalpartida);
                    $totalcosto = $totalcosto + $costototalpartida;
                    $totalutilidad = $totalutilidad + $utilidadpartida;
                }
                $CompraAnterior = Compra::where('Compra', $request->compradesactivar)->first();
                $OrdenTrabajoAnterior = OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)->first();
                OrdenTrabajo::where('Orden', $CompraAnterior->OrdenTrabajo)
                            ->update([
                                'Importe' => $OrdenTrabajoAnterior->Importe - $CompraAnterior->Importe,
                                'Descuento' => $OrdenTrabajoAnterior->Descuento - $CompraAnterior->Descuento,
                                'SubTotal' => $OrdenTrabajoAnterior->SubTotal - $CompraAnterior->SubTotal,
                                'Iva' => $OrdenTrabajoAnterior->Iva - $CompraAnterior->Iva,
                                'Total' => $OrdenTrabajoAnterior->Total - $CompraAnterior->Total,
                                'Costo' => $OrdenTrabajoAnterior->Costo - Helpers::convertirvalorcorrecto($totalcosto),
                                'Utilidad' => $OrdenTrabajoAnterior->Utilidad - Helpers::convertirvalorcorrecto($totalutilidad)
                            ]);
                break;
        }
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Compra::where('Compra', $request->compradesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Orden' => '',
                    'Importe' => '0.000000',
                    'Descuento' => '0.000000',
                    'Ieps' => '0.000000',
                    'SubTotal' => '0.000000',
                    'Iva' => '0.000000',
                    'IvaRetencion' => '0.000000',
                    'IsrRetencion' => '0.000000',
                    'IepsRetencion' => '0.000000',
                    'ImpLocRetenciones' => '0.000000',
                    'ImpLocTraslados' => '0.000000',
                    'Total' => '0.000000',
                    'Abonos' => '0.000000',
                    'Descuentos' => '0.000000',
                    'Saldo' => '0.000000'
                ]);
        $detalles = CompraDetalle::where('Compra', $request->compradesactivar)->get();
        foreach($detalles as $detalle){
            //validad el tipo de compra que es
            switch ($Compra->Tipo){
                case "TOT":
                    $eliminarrefacciones = OrdenTrabajoDetalle::where('Compra', $request->compradesactivar)->where('Codigo', $detalle->Codigo)->forceDelete();
                    break;
                case "GASTOS":
                    break;
                case "CAJA CHICA":
                    if($Compra->Almacen > 0){
                        //restar existencias al almacen
                        $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Compra->Almacen)->first();
                        $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$detalle->Cantidad;
                        Existencia::where('Codigo', $detalle->Codigo)
                                    ->where('Almacen', $Compra->Almacen)
                                    ->update([
                                        'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                    ]);
                    }
                    break;
                default:
                    //restar existencias al almacen
                    $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Compra->Almacen)->first();
                    $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$detalle->Cantidad;
                    Existencia::where('Codigo', $detalle->Codigo)
                                ->where('Almacen', $Compra->Almacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                                ]);
            }
            //modificar faltante por surtir detalle orden de compra
            $OrdenCompraDetalle = OrdenCompraDetalle::where('Codigo', $detalle->Codigo)->where('Orden', $Compra->Orden)->first();
            $Surtir = $OrdenCompraDetalle->Surtir+$detalle->Cantidad;
            OrdenCompraDetalle::where('Codigo', $detalle->Codigo)
                                ->where('Orden', $Compra->Orden)
                                ->update([
                                    'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                ]);
            //colocar en ceros cantidades
            CompraDetalle::where('Compra', $request->compradesactivar)
                            ->where('Item', $detalle->Item)
                            ->update([
                                'Cantidad' => '0.000000',
                                'Importe' => '0.000000',
                                //'Dcto' => '0.000000',
                                'Descuento' => '0.000000',
                                'ImporteDescuento' => '0.000000',
                                'Ieps' => '0.000000',
                                'SubTotal' => '0.000000',
                                'Iva' => '0.000000',
                                'IvaRetencion' => '0.000000',
                                'IsrRetencion' => '0.000000',
                                'IepsRetencion' => '0.000000',
                                'Total' => '0.000000',
                                'Orden' => ''
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "COMPRAS";
        $BitacoraDocumento->Movimiento = $request->compradesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Compra->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //colocar orden de compra en POR SURTIR
        OrdenCompra::where('Orden', $Compra->Orden)
                                    ->update([
                                        'Status' => "BACKORDER"
                                    ]);
        return response()->json($Compra);
    }
    //ver movimientos de compra
    public function compras_obtener_movimientos_compra(Request $request){
        $movimientoscxpcompra = CuentaXPagarDetalle::where('Compra', $request->compra)->get();
        $filasmovimientos = "";
        foreach($movimientoscxpcompra as $mcxpc){
            $colorfila = '';
            $CuentaXPagar = CuentaXPagar::where('Pago', $mcxpc->Pago)->first();
            if($CuentaXPagar->Status == 'BAJA'){
                $colorfila = 'bg-red';
            }
            $filasmovimientos= $filasmovimientos.
            '<tr class="'.$colorfila.'">'.
                '<td>CxP</td>'.
                '<td>'.$mcxpc->Pago.'</td>'.
                '<td>'.$mcxpc->Fecha.'</td>'.
                '<td>'.$mcxpc->Abono.'</td>'.
                '<td>'.$CuentaXPagar->Status.'</td>'.
            '</tr>';
        }
        $movimientosnpcompra = NotaProveedorDocumento::where('Compra', $request->compra)->get();
        foreach($movimientosnpcompra as $mnpc){
            $colorfila = '';
            $NotaProveedor = NotaProveedor::where('Nota', $mnpc->Nota)->first();
            if($NotaProveedor->Status == 'BAJA'){
                $colorfila = 'bg-red';
            }
            $filasmovimientos= $filasmovimientos.
            '<tr class="'.$colorfila.'">'.
                '<td>NC PROVEEDOR</td>'.
                '<td>'.$mnpc->Nota.'</td>'.
                '<td>'.$NotaProveedor->Fecha.'</td>'.
                '<td>'.$mnpc->Descuento.'</td>'.
                '<td>'.$NotaProveedor->Status.'</td>'.
            '</tr>';
        }
        $data = array(
            'filasmovimientos' => $filasmovimientos
        );
        return response()->json($data);
    }

    //buscar folio on key up
    public function compras_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaCompra::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        }
    }
    //generacion de formato en PDF
    public function compras_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $compras = Compra::where('Compra', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $compras = Compra::whereIn('Compra', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get();
            }else{
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $compras = Compra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(1500)->get();
                }else{
                    $compras = Compra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
                }
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($compras as $c){
            $data=array();
            $compradetalle = CompraDetalle::where('Compra', $c->Compra)->get();
            $datadetalle=array();
            foreach($compradetalle as $cd){
                $producto = Producto::where('Codigo', $cd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cd->Cantidad),
                    "codigodetalle"=>$cd->Codigo,
                    "descripciondetalle"=>$cd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $c->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Compras')->where('Documento', $c->Compra)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Compras')
            ->where('frd.Documento', $c->Compra)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "compra"=>$c,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentocompra"=>Helpers::convertirvalorcorrecto($c->Descuento),
                      "subtotalcompra"=>Helpers::convertirvalorcorrecto($c->SubTotal),
                      "ivacompra"=>Helpers::convertirvalorcorrecto($c->Iva),
                      "totalcompra"=>Helpers::convertirvalorcorrecto($c->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.compras.formato_pdf_compras', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$c->Compra.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($compras as $com){
            $ArchivoPDF = "PDF".$com->Compra.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
            array_push($arrayfilespdf,$ArchivoPDF);
        }
        $pdfMerger->merge(); //unirlos
        if($request->imprimirdirectamente == 1){
            $archivoacopiar = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $carpetacopias = public_path('xml_descargados/'.$ArchivoPDF);
            File::copy($archivoacopiar, $carpetacopias);
            return response()->json($ArchivoPDF);
        }else{
            if($request->descargar_xml == 0){
                $pdfMerger->save("Compras.pdf", "browser");//mostrarlos en el navegador
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
    }

    //generacion de formato en PDF
    public function compras_generar_pdfs_indiv($documento){
        $compras = Compra::where('Compra', $documento)->orderBy('Folio', 'ASC')->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($compras as $c){
            $compradetalle = CompraDetalle::where('Compra', $c->Compra)->get();
            $datadetalle=array();
            foreach($compradetalle as $cd){
                $producto = Producto::where('Codigo', $cd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cd->Cantidad),
                    "codigodetalle"=>$cd->Codigo,
                    "descripciondetalle"=>$cd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $c->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Compras')->where('Documento', $c->Compra)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Compras')
            ->where('frd.Documento', $c->Compra)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "compra"=>$c,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentocompra"=>Helpers::convertirvalorcorrecto($c->Descuento),
                      "subtotalcompra"=>Helpers::convertirvalorcorrecto($c->SubTotal),
                      "ivacompra"=>Helpers::convertirvalorcorrecto($c->Iva),
                      "totalcompra"=>Helpers::convertirvalorcorrecto($c->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.compras.formato_pdf_compras', compact('data'))
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
    public function compras_obtener_datos_envio_email(Request $request){
        $compra = Compra::where('Compra', $request->documento)->first();
        $proveedor = Proveedor::where('Numero',$compra->Proveedor)->first();
        $email2cc = '';
        $email3cc = '';
        if($proveedor->Email2 != '' || $proveedor->Email2 != null){
            $email2cc = $proveedor->Email2;
        }
        if($proveedor->Email3 != '' || $proveedor->Email3 != null){
            $email3cc = $proveedor->Email3;
        }
        $data = array(
            'compra' => $compra,
            'proveedor' => $proveedor,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $proveedor->Email1,
            'email2cc' => $email2cc,
            'email3cc' => $email3cc,
            'correodefault1enviodocumentos' => $this->correodefault1enviodocumentos,
            'correodefault2enviodocumentos' => $this->correodefault2enviodocumentos
        );
        return response()->json($data);
    }

    //enviar pdf por emial
    public function compras_enviar_pdfs_email(Request $request){
        $compras = Compra::where('Compra', $request->emaildocumento)->orderBy('Folio', 'ASC')->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($compras as $c){
            $compradetalle = CompraDetalle::where('Compra', $c->Compra)->get();
            $datadetalle=array();
            foreach($compradetalle as $cd){
                $producto = Producto::where('Codigo', $cd->Codigo)->first();
                $marca = Marca::where('Numero', $producto->Marca)->first();
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($cd->Cantidad),
                    "codigodetalle"=>$cd->Codigo,
                    "descripciondetalle"=>$cd->Descripcion,
                    "marcadetalle"=>$marca->Nombre,
                    "ubicaciondetalle"=>$producto->Ubicacion,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($cd->Precio),
                    "descuentodetalle" => Helpers::convertirvalorcorrecto($cd->Dcto),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($cd->SubTotal)
                );
            }
            $proveedor = Proveedor::where('Numero', $c->Proveedor)->first();
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Compras')->where('Documento', $c->Compra)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Compras')
            ->where('frd.Documento', $c->Compra)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "compra"=>$c,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentocompra"=>Helpers::convertirvalorcorrecto($c->Descuento),
                      "subtotalcompra"=>Helpers::convertirvalorcorrecto($c->SubTotal),
                      "ivacompra"=>Helpers::convertirvalorcorrecto($c->Iva),
                      "totalcompra"=>Helpers::convertirvalorcorrecto($c->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.compras.formato_pdf_compras', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = Compra::where('Compra', $request->emaildocumento)->first();
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
            $body = $request->emailmensaje;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CompraNo".$emaildocumento.".pdf")
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
                            ->attachData($pdf->output(), "CompraNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto)) {
                    unlink($urlarchivoadjunto);
                }
            }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CompraNo".$emaildocumento.".pdf")
                            ->attach($urlarchivoadjunto2);
                });
                //eliminar xml de storage/xml_cargados
                if (file_exists($urlarchivoadjunto2)) {
                    unlink($urlarchivoadjunto2);
                }
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CompraNo".$emaildocumento.".pdf");
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

    //exportar a excel
    public function compras_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Compras', Auth::user()->id);
        return Excel::download(new ComprasExport($configuraciones_tabla['campos_consulta'],$request->periodo), "compras-".$request->periodo.".xlsx");
    }
    public function compra_detalles_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $campos_consulta = [];
        array_push($campos_consulta, 'Compra');
        array_push($campos_consulta, 'Fecha');
        array_push($campos_consulta, 'Codigo');
        array_push($campos_consulta, 'Descripcion');
        array_push($campos_consulta, 'OT');
        array_push($campos_consulta, 'Unidad');
        array_push($campos_consulta, 'Cantidad');
        array_push($campos_consulta, 'Precio');
        array_push($campos_consulta, 'Importe');
        array_push($campos_consulta, 'SubTotal');
        array_push($campos_consulta, 'Iva');
        array_push($campos_consulta, 'Total');
        array_push($campos_consulta, 'Costo');
        return Excel::download(new ComprasDetallesExport($campos_consulta,$request->compra), "compra-".$request->compra.".xlsx");

            //dd($request->compra);
        }
    //generar excel compras individual
    public function compras_generar_excel_indiv($documento){
        return Excel::download(new PlantillaCompraExport($documento), "compra-".$documento.".xlsx");
    }
    //configuracion tabla
    public function compras_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Compras', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Compras')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Compras';
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
        return redirect()->route('compras');
    }

}
