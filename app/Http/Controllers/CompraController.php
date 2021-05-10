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
use App\ContraRecibo;
use App\ContraReciboDetalle;

class CompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'Compras')->first();
        $this->campos_consulta = [];
        foreach (explode(",", $this->configuracion_tabla->columnas_ordenadas) as $campo){
            array_push($this->campos_consulta, $campo);
        }
        //FIN CONFIGURACIONES DE LA TABLA//
    }

    public function compras(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->user, 'Compras');
        $configuracion_tabla = $this->configuracion_tabla;
        $rutaconfiguraciontabla = route('compras_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('compras_exportar_excel');
        $rutacreardocumento = route('compras_generar_pdfs');
        return view('registros.compras.compras', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento'));
    }
    //obtener todos los registros
    public function compras_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCompra::select($this->campos_consulta)->orderBy('Folio', 'DESC')->where('Periodo', $periodo)->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fechahoy,$tipousuariologueado){
                        $botoncambios =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" data-placement="top" title="Cambios" onclick="obtenerdatos(\''.$data->Compra.'\')"><i class="material-icons">mode_edit</i></div> '; 
                        $botonbajas =      '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Compra.'\')"><i class="material-icons">cancel</i></div> ';
                        $botonmvtos =      '<div class="btn bg-indigo btn-xs waves-effect" data-toggle="tooltip" title="Movimientos" onclick="movimientoscompra(\''.$data->Compra.'\')"><i class="material-icons">list</i></div>';
                        $operaciones =  $botoncambios.$botonbajas.$botonmvtos;
                        return $operaciones;
                    })
                    ->addColumn('SubTotal', function($data){ return $data->SubTotal; })
                    ->addColumn('Iva', function($data){ return $data->Iva; })
                    ->addColumn('Total', function($data){ return $data->Total; })
                    ->addColumn('Abonos', function($data){ return $data->Abonos; })
                    ->addColumn('Descuentos', function($data){ return $data->Descuentos; })
                    ->addColumn('Saldo', function($data){ return $data->Saldo; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    ->addColumn('Descuento', function($data){ return $data->Descuento; })
                    ->addColumn('Importe', function($data){ return $data->Importe; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        } 
    }
    //obtener el ultimo folio de la tabla
    public function compras_obtener_ultimo_folio(){
        $folio = Helpers::ultimofoliotablamodulos('App\Compra');
        return response()->json($folio);
    }
    //obtener tipos ordenes de compra
    public function compras_obtener_tipos_ordenes_compra(){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option selected disabled hidden>Selecciona...</option>";
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
            //detalles xml
            $filasdetallesxml = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
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
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$atributos_concepto['NoIdentificacion'].'" readonly>'.$atributos_concepto['NoIdentificacion'].'</td>'.
                    '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" value="'.$atributos_concepto['Descripcion'].'" readonly>'.$atributos_concepto['Descripcion'].'</div></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$atributos_concepto['Unidad'].'" readonly>'.$atributos_concepto['Unidad'].'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Cantidad']).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($atributos_concepto['Cantidad']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['ValorUnitario']).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($descuentoporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_concepto['Descuento']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($atributos_traslado['Base']+$atributos_traslado['Importe']).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-md-10">'.    
                                '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" readonly><input type="text" class="form-control divorinputmodmd departamentopartida" name="departamentopartida[]" readonly>'.   
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly></td>'.
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
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarproveedor('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc .'\')">Seleccionar</div>';
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
    //obtener productos
    public function compras_obtener_productos(Request $request){
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
                        //claveproducto
                        $claveproducto = ClaveProdServ::where('Clave', $data->ClaveProducto)->first();
                        //claveunidad
                        $claveunidad = ClaveUnidad::where('Clave', $data->ClaveUnidad)->first();
                        //obtener existencias del codigo en el almacen seleccionado
                        $ContarExistencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacen)->count();
                        if($ContarExistencia > 0){
                            $Existencia = Existencia::where('Codigo', $data->Codigo)->where('Almacen', $numeroalmacen)->first();
                            $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
                        }else{
                            $Existencias = 0;
                        }
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$claveproducto->Nombre.'\',\''.$claveunidad->Nombre.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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

    //obtener ordenes de compra por proveedor
    public function compras_obtener_ordenes_compra(Request $request){
        if($request->ajax()){
            $data = OrdenCompra::where('Proveedor', $request->numeroproveedor)
                                ->where('AutorizadoPor', '<>', '')
                                ->where(function ($query) {
                                    $query->where('Status', 'POR SURTIR')
                                    ->orWhere('Status', 'BACKORDER');
                                })
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarordencompra('.$data->Folio.',\''.$data->Orden .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->rawColumns(['operaciones','Fecha'])
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
            $claveabuscar = $request->claveabuscar;
            $data = ClaveProdServ::where('Usual', 'S')->where('Clave', 'like', '%' . $claveabuscar . '%')->get();
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
            $claveabuscar = $request->claveabuscar;
            $data = ClaveUnidad::where('Usual', 'S')->where('Clave', 'like', '%' . $claveabuscar . '%')->get();
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
                    $filasdetallesordencompra= $filasdetallesordencompra.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$doc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$doc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$doc->Codigo.'</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" value="'.$doc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$doc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$doc->Unidad.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$doc->Orden.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodxl">'.
                                '<div class="col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-md-10">'.    
                                    '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" readonly><input type="text" class="form-control divorinputmodmd departamentopartida" name="departamentopartida[]" readonly>'.   
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly data-parsley-length="[1, 5]"></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$claveunidadpartida->Nombre.'" readonly></td>'.
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
            "fecha" => Helpers::formatoinputdate($ordencompra->Fecha),
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
        ini_set('max_input_vars','10000' );
        $uuid=$request->uuid;
	    $ExisteUUID = Compra::where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true){
	        $Compra = 1;
	    }else{  
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofoliotablamodulos('App\Compra');
            //INGRESAR DATOS A TABLA COMPRAS
            $compra = $folio.'-'.$request->serie;
            $Compra = new Compra;
            $Compra->Compra=$compra;
            $Compra->Serie=$request->serie;
            $Compra->Folio=$request->folio;
            $Compra->Proveedor=$request->numeroproveedor;
            $Compra->Movimiento="ALMACEN".$request->numeroalmacen;
            $Compra->Remision=$request->remision;
            $Compra->Factura=$request->factura;
            $Compra->UUID=$request->uuid;
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
            $Compra->FechaEmitida=$request->fechaemitida;
            $Compra->FechaTimbrado=$request->fechatimbrado;
            $Compra->EmisorRfc=$request->emisorrfc;
            $Compra->EmisorNombre=$request->emisornombre;
            $Compra->ReceptorRfc=$request->receptorrfc;
            $Compra->ReceptorNombre=$request->receptornombre;
            $Compra->Status="POR PAGAR";
            $Compra->Usuario=Auth::user()->user;
            $Compra->Periodo=$request->periodohoy;
            $Compra->save();
            //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
            $BitacoraDocumento = new BitacoraDocumento;
            $BitacoraDocumento->Documento = "COMPRAS";
            $BitacoraDocumento->Movimiento = $compra;
            $BitacoraDocumento->Aplicacion = "ALTA";
            $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $BitacoraDocumento->Status = "POR PAGAR";
            $BitacoraDocumento->Usuario = Auth::user()->user;
            //$BitacoraDocumento->Equipo = $request->equipo;
            $BitacoraDocumento->Periodo = $request->periodohoy;
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
                $Producto->{'Fecha Ultima Compra'} = Carbon::parse($request->fecha)->toDateTimeString();
                $Producto->{'Ultimo Costo'} = $request->preciopartida [$key];
                $Producto->save();
                //modificar faltante por surtir detalle orden de compra
                $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                $Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                    ->where('Codigo', $codigoproductopartida)
                                    ->update([
                                        'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                    ]);
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
        //detalles orden compra
        $detallescompra = CompraDetalle::where('Compra', $request->compramodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallescompra = CompraDetalle::where('Compra', $request->compramodificar)->count();
        $filasdetallescompra = '';
        if($numerodetallescompra > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo="modificacion";
            foreach($detallescompra as $dc){
                    $nombreclaveproductopartida = '';
                    $claveproductopartida = '';
                    $nombreclaveunidadpartida = '';
                    $claveunidadpartida = '';
                    $producto = Producto::where('Codigo', $dc->Codigo)->first();
                    $Existencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', $compra->Almacen)->first();
                    $parsleymax = $dc->Cantidad;
                    $cantidadpartidadetalleordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->where('Codigo', $dc->Codigo)->first();
                    $contarclaveproductopartida = ClaveProdServ::where('Clave', $dc->ClaveProducto)->count();
                    if($contarclaveproductopartida > 0){
                        $ClaveProdServ = ClaveProdServ::where('Clave', $dc->ClaveProducto)->first();
                        $nombreclaveproductopartida = $ClaveProdServ->Nombre;
                        $claveproductopartida = $ClaveProdServ->Clave;
                    }
                    $contarclaveunidadpartida = ClaveUnidad::where('Clave', $dc->ClaveUnidad)->count();
                    if($contarclaveunidadpartida > 0){
                        $ClaveUnidad = ClaveUnidad::where('Clave', $dc->ClaveUnidad)->first();
                        $nombreclaveunidadpartida = $ClaveUnidad->Nombre;
                        $claveunidadpartida = $ClaveUnidad->Clave;
                    }
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
                    $filasdetallescompra= $filasdetallescompra.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]">'.$dc->Codigo.'</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodxl nombreproductopartida" name="nombreproductopartida[]" value="'.$dc->Descripcion.'" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)"></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="hidden" class="form-control cantidadinicialpartida" name="cantidadinicialpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" >'.
                            '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                            '<input type="hidden" class="form-control operacionaritmetica" name="operacionaritmetica[]" >'.
                            '<input type="hidden" class="form-control cantidadoperacionaritmetica" name="cantidadoperacionaritmetica[]" >'.
                            '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.

                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.',\''.$tipo.'\');">'.
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$dc->Orden.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod">'.'<div class="row divorinputmodxl">'.'<div class="col-md-2">'.'<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.'</div>'.'<div class="col-md-10">'.    '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" value="'.$numerodepartamento.'" readonly><input type="text" class="form-control divorinputmodmd departamentopartida" name="departamentopartida[]" value="'.$nombredepartamento.'" readonly>'.   '</div>'.'</div>'.'</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->PrecioMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->DescuentoMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$nombreclaveproductopartida.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida.'" readonly data-parsley-length="[1, 5]"></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidadpartida.'" readonly></td>'.
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
            "fecha" => Helpers::formatoinputdate($compra->Fecha),
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
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //evaluar existencias en almacen
    public function compras_obtener_existencias_partida(Request $request){
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
    //guardar modificacion compra
    public function compras_guardar_modificacion(Request $request){
        ini_set('max_input_vars','10000' );
        $uuid=$request->uuid;
        $compra = $request->compra;
	    $ExisteUUID = Compra::where('Compra', '<>', $compra)->where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true){
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
                    //restar existencias a almacen principal
                    $RestarExistenciaAlmacen = Existencia::where('Codigo', $explode_d[1])->where('Almacen', $request->numeroalmacen)->first();
                    $RestarExistenciaNuevaAlmacen = $RestarExistenciaAlmacen->Existencias - $detallecompra->Cantidad;
                    Existencia::where('Codigo', $explode_d[1])
                                ->where('Almacen', $request->numeroalmacen)
                                ->update([
                                    'Existencias' => Helpers::convertirvalorcorrecto($RestarExistenciaNuevaAlmacen)
                                ]);
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
            Compra::where('Compra', $compra)
            ->update([
                'Proveedor'=>$request->numeroproveedor,
                'Movimiento'=>"ALMACEN".$request->numeroalmacen,
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
            $BitacoraDocumento->Periodo = $request->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){  
                //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
                if($request->agregadoen [$key] == 'modificacion'){         
                    $item = CompraDetalle::select('Item')->where('Compra', $compra)->orderBy('Item', 'DESC')->take(1)->get();
                    $ultimoitem = $item[0]->Item+1;
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
                    $Producto->{'Fecha Ultima Compra'} = Carbon::parse($request->fecha)->toDateTimeString();
                    $Producto->{'Ultimo Costo'} = $request->preciopartida [$key];
                    $Producto->save();
                    //modificar faltante por surtir detalle orden de compra
                    $OrdenCompraDetalle = OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])->where('Codigo', $codigoproductopartida)->first();
                    $Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                    OrdenCompraDetalle::where('Orden', $request->ordenpartida [$key])
                                        ->where('Codigo', $codigoproductopartida)
                                        ->update([
                                            'Surtir' => Helpers::convertirvalorcorrecto($Surtir)
                                        ]);
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
                    $ultimoitem++;
                }else{
                    //si la partida no se agrego en la modificacion solo se modifican los datos
                    //modificar detalle
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
        $numerocuentasporpagar = CuentaXPagarDetalle::where('Compra', $request->compradesactivar)->count();
        $numerocontrareciboscompra = ContraReciboDetalle::where('Compra', $request->compradesactivar)->count();
        $numerocuentaxpagar = 0;
        $numerocontrarecibo = 0;
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
        //verificar si el almacen cuenta con las existencias
        $comprabaja = Compra::where('Compra', $request->compradesactivar)->first();
        $detallescomprabaja = CompraDetalle::where('Compra', $request->compradesactivar)->get();
        foreach($detallescomprabaja as $detallecomprabaja){
            $existencias = Existencia::select('Existencias')->where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->first();
            if($detallecomprabaja->Cantidad > $existencias->Existencias){
                $numerodetallesconexistenciasinsuficientes++;
            }
        }
        $resultadofechas = Helpers::compararanoymesfechas($Compra->Fecha);
        $data = array (
            'numerocuentasporpagar' => $numerocuentasporpagar,
            'numerocuentaxpagar' => $numerocuentaxpagar,
            'numerocontrareciboscompra' => $numerocontrareciboscompra,
            'numerocontrarecibo' => $numerocontrarecibo,
            'numerodetallesconexistenciasinsuficientes' => $numerodetallesconexistenciasinsuficientes,
            'resultadofechas' => $resultadofechas,
            'Status' => $Compra->Status
        );
        return response()->json($data);
    }
    //dar de baja compra
    public function compras_alta_o_baja(Request $request){
        $Compra = Compra::where('Compra', $request->compradesactivar)->first();
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
                    'Descuentos' => '0.000000'
                ]);
        $detalles = CompraDetalle::where('Compra', $request->compradesactivar)->get();
        foreach($detalles as $detalle){
            //restar existencias al almacen
            $ExistenciaAlmacen = Existencia::where('Codigo', $detalle->Codigo)->where('Almacen', $Compra->Almacen)->first();
            $ExistenciaNuevaAlmacen = $ExistenciaAlmacen->Existencias-$detalle->Cantidad;
            Existencia::where('Codigo', $detalle->Codigo)
                        ->where('Almacen', $Compra->Almacen)
                        ->update([
                            'Existencias' => Helpers::convertirvalorcorrecto($ExistenciaNuevaAlmacen)
                        ]);
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
                                'Dcto' => '0.000000',
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
            $data = Compra::where('Compra', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Compra .'\')"><i class="material-icons">done</i></div> ';
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
    public function compras_generar_pdfs(Request $request){
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $compras = Compra::whereIn('Compra', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $compras = Compra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
        }
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
            $data[]=array(
                      "compra"=>$c,
                      "descuentocompra"=>Helpers::convertirvalorcorrecto($c->Descuento),
                      "subtotalcompra"=>Helpers::convertirvalorcorrecto($c->SubTotal),
                      "ivacompra"=>Helpers::convertirvalorcorrecto($c->Iva),
                      "totalcompra"=>Helpers::convertirvalorcorrecto($c->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
        }
        //dd($data);
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.compras.formato_pdf_compras', compact('data'))
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 5)
        ->setOption('margin-right', 5)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }
    //exportar a excel
    public function compras_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        return Excel::download(new ComprasExport($this->campos_consulta,$request->periodo), "compras-".$request->periodo.".xlsx");   
   
    }
    //configuracion tabla
    public function compras_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $Configuracion_Tabla = Configuracion_Tabla::where('tabla', 'Compras')->first();
        $Configuracion_Tabla->campos_activados = $request->string_datos_tabla_true;
        $Configuracion_Tabla->campos_desactivados = $string_datos_tabla_false;
        $Configuracion_Tabla->columnas_ordenadas = $request->string_datos_ordenamiento_columnas;
        $Configuracion_Tabla->usuario = Auth::user()->user;
        $Configuracion_Tabla->save();
        return redirect()->route('compras');
    }

}
