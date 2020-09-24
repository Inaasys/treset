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
use App\Exports\NotasCreditoProveedoresExport;
use App\NotaProveedor;
use App\NotaProveedorDetalle;
use App\Compra;
use App\CompraDetalle;
use App\Proveedor;
use App\Almacen;
use App\BitacoraDocumento;
Use App\Existencia;
use App\Configuracion_Tabla;
use App\VistaNotaCreditoProveedor;

class NotasCreditoProveedoresController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoProveedor')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }
    
    public function notas_credito_proveedores(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'ContraRecibos');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('notas_credito_proveedor_guardar_configuracion_tabla');
        $rutacreardocumento = route('notas_credito_proveedores_generar_pdfs');
        return view('registros.notascreditoproveedores.notascreditoproveedores', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','rutacreardocumento'));
    }
    //obtener registros tabla
    public function notas_credito_proveedores_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaNotaCreditoProveedor::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Folio', 'DESC')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        if($data->Status != 'BAJA'){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Nota .'\')"><i class="material-icons">mode_edit</i></div> '. 
                            '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Nota .'\')"><i class="material-icons">cancel</i></div>  ';
                        }else{
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Cambios" onclick="obtenerdatos(\''.$data->Nota .'\')"><i class="material-icons">mode_edit</i></div> ';
                        }
                        return $boton;
                    })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    ->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->addColumn('Importe', function($data){ return $data->Importe; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener ultimo folio
    public function notas_credito_proveedores_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofoliotablamodulos('App\NotaProveedor');
        return response()->json($folio);
    }
    //obtener proveedor
    public function notas_credito_proveedores_obtener_proveedores(Request $request){
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
    public function notas_credito_proveedores_obtener_almacenes(Request $request){
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
    //obtener compras
    public function notas_credito_proveedores_obtener_compras(Request $request){
        if($request->ajax()){
            $data = Compra::where('Proveedor', $request->numeroproveedor)
                                /*->where('AutorizadoPor', '<>', '')
                                ->where(function ($query) {
                                    $query->where('Status', 'POR SURTIR')
                                        ->orWhere('Status', 'BACKORDER');
                                })*/
                                ->orderBy('Folio', 'ASC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcompra('.$data->Folio.',\''.$data->Compra .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones','Fecha','Total'])
                    ->make(true);
        }
    }
    //obtener compra seleccionada
    public function notas_credito_proveedores_obtener_compra(Request $request){
        $compra = Compra::where('Compra', $request->Compra)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($compra->Iva, $compra->SubTotal);
        //detalles orden compra
        $filadetallescompra = '';
        $filadetallescompra= $filadetallescompra.
        '<tr class="filasproductos" id="filaproducto'.$request->contadorfilas.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfila" onclick="eliminarfilanotaproveedor('.$request->contadorfilas.')" >X</div></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturapartida" name="facturapartida[]" value="'.$compra->Factura.'/'.$compra->Remision.'" readonly><input type="hidden" class="form-control comprapartida" name="comprapartida[]" value="'.$compra->Compra.'" readonly>'.$compra->Compra.'</td>'.
            '<td class="tdmod"><div class="divorinputmodxl"><input type="hidden" class="form-control uuidpartida" name="uuidpartida[]" value="'.$compra->UUID.'" readonly>'.$compra->UUID.'</div></td>'.
            '<td class="tdmod">'.
                '<div class="row divorinputmodxl">'.
                    '<div class="col-md-2">'.
                        '<div class="btn bg-blue btn-xs waves-effect btnlistarcodigoscompra" data-toggle="tooltip" title="Ver Códigos" onclick="listarcodigoscompra('.$request->contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                    '</div>'.
                    '<div class="col-md-10">'.    
                        '<input type="text" class="form-control divorinputmodmd codigopartida" name="codigopartida[]" value="DPPP" >'.
                    '</div>'.
                '</div>'.
            '</td>'.            
            '<td class="tdmod"><div class="divorinputmodl"><input type="text" class="form-control divorinputmodl descripcionpartida" name="descripcionpartida[]" value="DESCUENTO POR PRONTO PAGO" ></div></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="NA" ></td>'.
            '<td class="tdmod">'.
                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-min="0.1" data-parsley-max="1.0"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');revisarexistenciasalmacen('.$request->contadorfilas.');">'.
                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="0" >'.
                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
            '</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($compra->SubTotal).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($compra->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" ></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($compra->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($compra->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeiva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$request->contadorfilas.');" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($compra->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="0"></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="01010101" readonly></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="No existe en el catálogo" readonly></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="H87" readonly></td>'.
            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="Pieza" readonly></td>'.
        '</tr>';
        $data = array(
            "compra" => $compra,
            "filadetallescompra" => $filadetallescompra,
        );
        return response()->json($data);
    }

    //obtener los codigos de la compra seleccionada
    public function notas_credito_proveedor_obtener_codigos_compra(Request $request){
        if($request->ajax()){
            $fila = $request->fila;            
            $data = DB::table('Compras Detalles AS cd')
                        ->Join('c_ClaveProdServ AS cp', 'cd.ClaveProducto', '=', 'cp.Clave')
                        ->Join('c_ClaveUnidad AS cu', 'cd.ClaveUnidad', '=', 'cu.Clave')
                        ->select('cd.Item AS Item', 'cd.Compra AS Compra', 'cd.Codigo AS Codigo', 'cd.Descripcion AS Descripcion', 'cd.Unidad AS Unidad', 'cd.Cantidad AS Cantidad', 'cd.Precio AS Precio', 'cd.Importe AS Importe', 'cd.Dcto AS Dcto', 'cd.Descuento AS Descuento', 'cd.ImporteDescuento AS ImporteDescuento', 'cd.SubTotal AS SubTotal', 'cd.Impuesto AS Impuesto', 'cd.Iva AS Iva', 'cd.Total AS Total', 'cp.Clave AS ClaveProducto', 'cp.Nombre AS NombreClaveProducto', 'cu.Clave AS ClaveUnidad', 'cu.Nombre AS NombreClaveUnidad')
                        ->where('cd.Compra', $request->compra)
                        ->orderBy('cd.Item', 'ASC')
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcodigocompra('.$fila.','.$data->Item.',\''.$data->Compra.'\',\''.$data->Codigo.'\',\''.$data->Descripcion.'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\',\''.Helpers::convertirvalorcorrecto($data->Precio).'\',\''.Helpers::convertirvalorcorrecto($data->Importe).'\',\''.Helpers::convertirvalorcorrecto($data->Dcto).'\',\''.Helpers::convertirvalorcorrecto($data->Descuento).'\',\''.Helpers::convertirvalorcorrecto($data->ImporteDescuento).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->Iva).'\',\''.Helpers::convertirvalorcorrecto($data->Total).'\',\''.$data->ClaveProducto.'\',\''.$data->NombreClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveUnidad.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Cantidad', function($data){
                        return Helpers::convertirvalorcorrecto($data->Cantidad);
                    })
                    ->addColumn('Precio', function($data){
                        return Helpers::convertirvalorcorrecto($data->Precio);
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones','Cantidad','Precio','Total'])
                    ->make(true);
        }
    }
    

    public function notas_credito_proveedor_cargar_xml_alta(Request $request){
        $mover_a_carpeta="xml_cargados";
        $xml = $request->xml;
        $nombre_original = $xml->getClientOriginalName();
        //guardar xml en public/xml_cargados
        $xml->move($mover_a_carpeta,$nombre_original);
        if (file_exists('xml_cargados/'.$nombre_original)) {
            //cargar xml
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
            $impuesto = $namespaces->Impuestos->attributes();
            $TotalImpuestosTrasladados = $impuesto['TotalImpuestosTrasladados'];
            //obtener datos generales del xml nodo hijo traslado del nodo padre Impuestos
            $array_traslados_impuestos = array();
            foreach($namespaces->Impuestos->Traslados->Traslado as $traslado){
                $atributos_traslado = $traslado->attributes();
                $array_traslados_impuestos[] = array(
                    "Impuesto" => $atributos_traslado['Impuesto'],
                    "TipoFactor" => $atributos_traslado['TipoFactor'],
                    "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                    "Importe" => $atributos_traslado['Importe']
                );
            }
            //obtener todas las partidas ó conceptos del xml
            $array_conceptos = array();
            foreach($namespaces->Conceptos->Concepto as $concepto){
                //obtener datos generales del xml nodo hijo traslado del nodo padre Concepto
                $array_traslados = array();
                $atributos_traslado = $concepto->Impuestos->Traslados->Traslado->attributes();
                $array_traslados[] = array(
                    "Base" => $atributos_traslado['Base'],
                    "Impuesto" => $atributos_traslado['Impuesto'],
                    "TipoFactor" => $atributos_traslado['TipoFactor'],
                    "TasaOCuota" => $atributos_traslado['TasaOCuota'],
                    "Importe" => $atributos_traslado['Importe']
                );
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
                    "array_traslados" => $array_traslados
                );   
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
                "fechatimbrado" => $fechatimbrado    
            );
            //eliminar xml de public/xml_cargados
            $eliminarxml = public_path().'/xml_cargados/'.$nombre_original;
            unlink($eliminarxml);
        } else {
            exit('Error al abrir xml.');
        }
        return response()->json($data);
    }

    //obtener existencias actuales por codigo y almacen
    public function notas_credito_proveedor_obtener_existencias_partida(Request $request){
        $existencias = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
        return response()->json(Helpers::convertirvalorcorrecto($existencias->Existencias));
    }

    public function notas_credito_proveedor_guardar(Request $request){
        $uuid=$request->uuid;
	    $ExisteUUID = NotaProveedor::where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true){
	        $NotaProveedor = 1;
	    }else{  
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofoliotablamodulos('App\NotaProveedor');
            //INGRESAR DATOS A TABLA COMPRAS
            $notaproveedor = $folio.'-'.$request->serie;
            $NotaProveedor = new NotaProveedor;
            $NotaProveedor->Nota=$notaproveedor;
            $NotaProveedor->Serie=$request->serie;
            $NotaProveedor->Folio=$folio;
            $NotaProveedor->Proveedor=$request->numeroproveedor;
            $NotaProveedor->NotaProveedor=$request->notaproveedor;
            $NotaProveedor->UUID=$request->uuid;
            $NotaProveedor->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $NotaProveedor->Almacen=$request->numeroalmacen; 
            $NotaProveedor->Importe=$request->importe;
            $NotaProveedor->Descuento=$request->descuento;
            $NotaProveedor->Ieps=$request->ieps;  
            $NotaProveedor->SubTotal=$request->subtotal;
            $NotaProveedor->Iva=$request->iva;
            $NotaProveedor->IvaRetencion=$request->retencioniva;
            $NotaProveedor->IsrRetencion=$request->retencionisr;
            $NotaProveedor->IepsRetencion=$request->retencionieps;
            $NotaProveedor->Total=$request->total;
            $NotaProveedor->Obs=$request->observaciones;
            $NotaProveedor->Moneda=$request->moneda;
            $NotaProveedor->TipoCambio=$request->pesosmoneda;
            $NotaProveedor->FechaEmitida=NULL;
            $NotaProveedor->EmisorRfc=$request->emisorrfc;
            $NotaProveedor->EmisorNombre=$request->emisornombre;
            $NotaProveedor->ReceptorRfc=$request->receptorrfc;
            $NotaProveedor->ReceptorNombre=$request->receptornombre;
            $NotaProveedor->Status="ALTA";
            $NotaProveedor->Usuario=Auth::user()->user;
            $NotaProveedor->Periodo=$request->periodohoy;
            $NotaProveedor->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "NOTAS PROVEEDOR";
            $BitacoraDocumento->Movimiento = $notaproveedor;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "ALTA";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            $item = 1;
            foreach ($request->codigopartida as $key => $codigopartida){             
                $NotaProveedorDetalle=new NotaProveedorDetalle;
                $NotaProveedorDetalle->Nota = $notaproveedor;
                $NotaProveedorDetalle->Proveedor = $request->numeroproveedor;
                $NotaProveedorDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $NotaProveedorDetalle->Compra = $request->comprapartida [$key];
                $NotaProveedorDetalle->Factura = $request->facturapartida [$key];
                $NotaProveedorDetalle->UUID = $request->uuidpartida [$key];
                $NotaProveedorDetalle->Codigo = $codigopartida;
                $NotaProveedorDetalle->Descripcion = $request->descripcionpartida [$key];
                $NotaProveedorDetalle->Unidad = $request->unidadpartida [$key];
                $NotaProveedorDetalle->Cantidad =  $request->cantidadpartida  [$key];
                $NotaProveedorDetalle->Precio =  $request->preciopartida [$key];
                $NotaProveedorDetalle->Importe = $request->importepartida [$key];
                $NotaProveedorDetalle->Dcto = $request->descuentoporcentajepartida [$key];
                $NotaProveedorDetalle->Descuento = $request->descuentopesospartida [$key];
                $NotaProveedorDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
                $NotaProveedorDetalle->Ieps = $request->trasladoiepspesospartida [$key];
                $NotaProveedorDetalle->SubTotal = $request->subtotalpartida [$key];
                $NotaProveedorDetalle->Impuesto = $request->ivaporcentajepartida [$key];
                $NotaProveedorDetalle->Iva = $request->trasladoivapesospartida [$key];
                $NotaProveedorDetalle->IvaRetencion = $request->retencionivapesospartida [$key];
                $NotaProveedorDetalle->IsrRetencion = $request->retencionisrpesospartida [$key];
                $NotaProveedorDetalle->IepsRetencion = $request->retencioniepspesospartida [$key];
                $NotaProveedorDetalle->Total = $request->totalpesospartida [$key];
                $NotaProveedorDetalle->Partida = $request->partida [$key];
                $NotaProveedorDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $NotaProveedorDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $NotaProveedorDetalle->Item = $item;
                $NotaProveedorDetalle->save();
                //modificar las existencias del código en la tabla de existencias
                $Existencia = Existencia::where('Codigo', $codigopartida)->where('Almacen', $request->numeroalmacen)->first();
                $Existencia->Codigo = $codigopartida;
                $Existencia->Almacen = $request->numeroalmacen;
                $Existencia->Existencias = $Existencia->Existencias-$request->cantidadpartida  [$key];
                $Existencia->save();
                $item++;
            }
            
            /*
            //modificar el status de la orden de compra a SURTIDO o BACKORDER
            $detallesordenporsurtir = OrdenCompraDetalle::where('Orden', $request->orden)->where('Surtir', '>', 0)->count();
            $OrdenCompra = OrdenCompra::where('Orden', $request->orden)->first();
            if($detallesordenporsurtir > 0){
                $OrdenCompra->Status = "BACKORDER";
            }else{
                $OrdenCompra->Status = "SURTIDO";
            }
            $OrdenCompra->save();
            */

        }    
            return response()->json($NotaProveedor);   
    }


    //buscar folio on key up
    public function notas_credito_proveedores_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = NotaProveedor::where('Nota', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Nota.'\')"><i class="material-icons">done</i></div> ';
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
    public function notas_credito_proveedores_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $notascreditoproveedor = NotaProveedor::whereIn('Nota', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $notascreditoproveedor = NotaProveedor::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($notascreditoproveedor as $ncp){
            $notascreditoproveedordetalle = NotaProveedorDetalle::where('Nota', $ncp->Nota)->get();
            $datadetalle=array();
            foreach($notascreditoproveedordetalle as $ncpd){
                $contarcompradetalle = Compra::where('Compra', $ncpd->Compra)->count();
                $compradetalle = Compra::where('Compra', $ncpd->Compra)->first();
                if($contarcompradetalle == 0){
                    $remisiondetalle = "";
                    $facturadetalle = "";
                }else{
                    $remisiondetalle = $compradetalle->Remision;
                    $facturadetalle = $compradetalle->Factura;
                }
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($ncpd->Cantidad),
                    "codigodetalle"=>$ncpd->Codigo,
                    "descripciondetalle"=>$ncpd->Descripcion,
                    "compradetalle"=>$ncpd->Compra,
                    "remisiondetalle"=>$remisiondetalle,
                    "facturadetalle"=>$facturadetalle,
                    "preciodetalle" => Helpers::convertirvalorcorrecto($ncpd->Precio),
                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($ncpd->SubTotal)
                );
            } 
            $proveedor = Proveedor::where('Numero', $ncp->Proveedor)->first();
            $data[]=array(
                "notacreditoproveedor"=>$ncp,
                "descuentonotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Descuento),
                "subtotalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->SubTotal),
                "ivanotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Iva),
                "totalnotacreditoproveedor"=>Helpers::convertirvalorcorrecto($ncp->Total),
                "proveedor" => $proveedor,
                "datadetalle" => $datadetalle
            );
        }
        //$footerHtml = view()->make('seccionespdf.footer', compact('fechaformato'))->render();
        $pdf = PDF::loadView('registros.notascreditoproveedores.formato_pdf_notascreditoproveedores', compact('data'))
        //->setOption('footer-html', $footerHtml, 'Página [page]')
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }
    //exportar a excel
    public function notas_credito_proveedores_exportar_excel(){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new NotasCreditoProveedoresExport($this->campos_consulta), "notascreditoproveedores.xlsx");  
    }
    //configuracion tabla
    public function notas_credito_proveedor_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'NotasCreditoProveedor')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('notas_credito_proveedores');
    }
}
