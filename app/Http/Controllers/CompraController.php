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
use App\Marca;

class CompraController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function compras(){
        $serieusuario = Helpers::obtenerserieusuario(Auth::user()->email, 'Compras');
        return view('registros.compras.compras', compact('serieusuario'));
    }
    //obtener todos los registros
    public function compras_obtener(Request $request){
        if($request->ajax()){
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = DB::table('Compras AS c')
            ->Join('Proveedores AS p', 'c.Proveedor', '=', 'p.Numero')
            ->select('c.Compra AS Compra', 'c.Serie AS Serie', 'c.Folio AS Folio', 'c.Proveedor AS Proveedor', 'p.Nombre AS Nombre', 'c.Plazo AS Plazo', 'c.Fecha AS Fecha', 'c.FechaEmitida AS FechaEmitida', 'c.Remision AS Remision', 'c.Factura AS Factura', 'c.Tipo AS Tipo', 'c.Almacen AS Almacen', 'c.Movimiento AS Movimiento', 'c.UUID AS UUID', 'c.Orden AS Orden', 'c.SubTotal AS SubTotal', 'c.Iva AS Iva', 'c.Total AS Total', 'c.Abonos AS Abonos', 'c.Descuentos AS Descuentos', 'c.Saldo AS Saldo', 'c.TipoCambio AS TipoCambio', 'c.Obs AS Obs', 'c.Equipo AS Equipo', 'c.Usuario AS Usuario', 'c.Status AS Status', 'c.Periodo AS Periodo')
            ->where('c.Periodo', $periodo)
            ->orderBy('c.Folio', 'DESC')
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fechahoy,$tipousuariologueado){
                        if($data->Status == "LIQUIDADA" && $tipousuariologueado <> 1){
                            $boton =    '<div class="btn bg-indigo btn-xs waves-effect" data-toggle="tooltip" title="Movimientos" onclick="movimientoscompra(\''.$data->Compra.'\')"><i class="material-icons">list</i></div>';
                        }else if($tipousuariologueado == 1){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" data-placement="top" title="Cambios" onclick="obtenerdatos(\''.$data->Compra.'\')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Compra.'\')"><i class="material-icons">cancel</i></div> '.
                                        '<div class="btn bg-indigo btn-xs waves-effect" data-toggle="tooltip" title="Movimientos" onclick="movimientoscompra(\''.$data->Compra.'\')"><i class="material-icons">list</i></div>';
                        }else if($tipousuariologueado <> 1 && $fechahoy == Carbon::parse($data->Fecha)->toDateString()){
                            $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" data-placement="top" title="Cambios" onclick="obtenerdatos(\''.$data->Compra.'\')"><i class="material-icons">mode_edit</i></div> '. 
                                        '<div class="btn bg-deep-orange btn-xs waves-effect" data-toggle="tooltip" title="Bajas" onclick="desactivar(\''.$data->Compra.'\')"><i class="material-icons">cancel</i></div> '.
                                        '<div class="btn bg-indigo btn-xs waves-effect" data-toggle="tooltip" title="Movimientos" onclick="movimientoscompra(\''.$data->Compra.'\')"><i class="material-icons">list</i></div>';
                        }else{
                            $boton =    '<div class="btn bg-indigo btn-xs waves-effect" data-toggle="tooltip" title="Movimientos" onclick="movimientoscompra(\''.$data->Compra.'\')"><i class="material-icons">list</i></div>';
                        }
                        return $boton;
                    })
                    ->addColumn('SubTotal', function($data){
                        $subtotal = Helpers::convertirvalorcorrecto($data->SubTotal);
                        return $subtotal;
                    })
                    ->addColumn('Iva', function($data){
                        $iva = Helpers::convertirvalorcorrecto($data->Iva);
                        return $iva;
                    })
                    ->addColumn('Total', function($data){
                        $total = Helpers::convertirvalorcorrecto($data->Total);
                        return $total;
                    })
                    ->addColumn('Abonos', function($data){
                        $abonos = Helpers::convertirvalorcorrecto($data->Abonos);
                        return $abonos;
                    })
                    ->addColumn('Descuentos', function($data){
                        $descuentos = Helpers::convertirvalorcorrecto($data->Descuentos);
                        return $descuentos;
                    })
                    ->addColumn('Saldo', function($data){
                        $saldo = Helpers::convertirvalorcorrecto($data->Saldo);
                        return $saldo;
                    })
                    ->addColumn('TipoCambio', function($data){
                        $tipocambio = Helpers::convertirvalorcorrecto($data->TipoCambio);
                        return $tipocambio;
                    })
                    ->rawColumns(['operaciones','SubTotal','Iva','Total','Abonos','Descuentos','Saldo','TipoCambio'])
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
    //obtener proveedores
    public function compras_obtener_proveedores(Request $request){
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
            foreach($detallesordencompra as $doc){
                $surtir = Helpers::convertirvalorcorrecto($doc->Surtir);
                if($surtir > 0){
                    $producto = Producto::where('Codigo', $doc->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $producto->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $producto->ClaveUnidad)->first();
                    $filasdetallesordencompra= $filasdetallesordencompra.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$doc->Item.'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$doc->Codigo.'" readonly>'.$doc->Codigo.'</td>'.
                        '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" value="'.$doc->Descripcion.'" readonly>'.$doc->Descripcion.'</div></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$doc->Unidad.'" readonly>'.$doc->Unidad.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Cantidad).'" data-parsley-min="0.1" data-parsley-max="'.Helpers::convertirvalorcorrecto($doc->Surtir).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');"></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Precio).'" data-parsley-min="0.1" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($doc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="0.'.$this->numerocerosconfigurados.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($doc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$doc->Orden.'" readonly></td>'.
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
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'"  readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
                }    
            }
        }else{
            $filasdetallesordencompra = '';
        }        
        $data = array(
            "ordencompra" => $ordencompra,
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
                $OrdenCompraDetalle->Surtir = $OrdenCompraDetalle->Surtir-$request->cantidadpartida  [$key];
                $OrdenCompraDetalle->save();
                //modificar las existencias del código en la tabla de existencias
                $ContarExistencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                if($ContarExistencia > 0){
                    $Existencia = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                }else{
                    $Existencia = new Existencia;
                }
                $Existencia->Codigo = $codigoproductopartida;
                $Existencia->Almacen = $request->numeroalmacen;
                $Existencia->Existencias = $Existencia->Existencias+$request->cantidadpartida  [$key];
                $Existencia->save();
                $item++;
            }
            //modificar el status de la orden de compra a SURTIDO o BACKORDER
            $detallesordenporsurtir = OrdenCompraDetalle::where('Orden', $request->orden)->where('Surtir', '>', 0)->count();
            $OrdenCompra = OrdenCompra::where('Orden', $request->orden)->first();
            if($detallesordenporsurtir > 0){
                $OrdenCompra->Status = "BACKORDER";
            }else{
                $OrdenCompra->Status = "SURTIDO";
            }
            $OrdenCompra->save();
        }    
            return response()->json($Compra);         
    }
    //obtener compra a modificar
    public function compras_obtener_compra(Request $request){
        $compra = Compra::where('Compra', $request->compramodificar)->first();
        $almacen = Almacen::where('Numero', $compra->Almacen)->first();
        $proveedor = Proveedor::where('Numero', $compra->Proveedor)->first();
        //detalles orden compra
        $detallescompra = CompraDetalle::where('Compra', $request->compramodificar)->get();
        $numerodetallescompra = CompraDetalle::where('Compra', $request->compramodificar)->count();
        $filasdetallescompra = '';
        if($numerodetallescompra > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            foreach($detallescompra as $dc){
                    $producto = Producto::where('Codigo', $dc->Codigo)->first();
                    $cantidadpartidadetalleordencompra = OrdenCompraDetalle::where('Orden', $compra->Orden)->where('Codigo', $dc->Codigo)->first();
                    $claveproductopartida = ClaveProdServ::where('Clave', $dc->ClaveProducto)->first();
                    $claveunidadpartida = ClaveUnidad::where('Clave', $dc->ClaveUnidad)->first();
                    $condepartamento = Departamento::where('Numero', $dc->Depto)->count();
                    $numerodepartamento = "";
                    $nombredepartamento = "";
                    if($condepartamento > 0){
                        $departamento = Departamento::where('Numero', $dc->Depto)->first();
                        $numerodepartamento = $departamento->Numero;
                        $nombredepartamento = $departamento->Nombre;
                    }
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
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" >X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dc->Item.'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly>'.$dc->Codigo.'</td>'.
                        '<td class="tdmod"><div class="divorinputmodl"><input type="hidden" class="form-control nombreproductopartida" name="nombreproductopartida[]" value="'.$dc->Descripcion.'" readonly>'.$dc->Descripcion.'</div></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly>'.$dc->Unidad.'</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm porsurtirpartida"  name="porsurtirpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod">'.
                            '<input type="hidden" class="form-control cantidadinicialpartida" name="cantidadinicialpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" >'.
                            '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                            '<input type="hidden" class="form-control operacionaritmetica" name="operacionaritmetica[]" >'.
                            '<input type="hidden" class="form-control cantidadoperacionaritmetica" name="cantidadoperacionaritmetica[]" >'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-max="'.Helpers::convertirvalorcorrecto($cantidadpartidadetalleordencompra->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');revisarexistenciasalmacen('.$contadorfilas.');" readonly>'.
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm iepsporcentajepartida" name="iepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoiepspesospartida" name="trasladoiepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Ieps).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivaporcentajepartida" name="retencionivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencioniva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionivapesospartida" name="retencionivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IvaRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrporcentajepartida" name="retencionisrporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionisr).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencionisrpesospartida" name="retencionisrpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IsrRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepsporcentajepartida" name="retencioniepsporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($porcentajeretencionieps).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilasordencompra('.$contadorfilas.');" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm retencioniepspesospartida" name="retencioniepspesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->IepsRetencion).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]" value="'.$dc->Orden.'" readonly></td>'.
                        '<td class="tdmod">'.'<div class="row divorinputmodxl">'.'<div class="col-md-2">'.'<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Ver Departamentos" onclick="listardepartamentos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.'</div>'.'<div class="col-md-10">'.    '<input type="hidden" class="form-control divorinputmodsm numerodepartamentopartida" name="numerodepartamentopartida[]" value="'.$numerodepartamento.'" readonly><input type="text" class="form-control divorinputmodmd departamentopartida" name="departamentopartida[]" value="'.$nombredepartamento.'" readonly>'.   '</div>'.'</div>'.'</td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciomonedapartida" name="preciomonedapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->PrecioMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopartida" name="descuentopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->DescuentoMoneda).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" ></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproductopartida->Clave.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$claveproductopartida->Nombre.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidadpartida->Clave.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$claveunidadpartida->Nombre.'" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costocatalogopartida" name="costocatalogopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'"  readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm costoingresadopartida" name="costoingresadopartida[]" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
            }
        }        
        $data = array(
            "compra" => $compra,
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
            "modificacionpermitida" => 1
        );
        return response()->json($data);
    }
    //evaluar existencias en almacen
    public function compras_obtener_existencias_partida(Request $request){
        $existencias = Existencia::select('Existencias')->where('Codigo', $request->codigopartida)->where('Almacen', $request->almacen)->first();
        return response()->json(Helpers::convertirvalorcorrecto($existencias->Existencias));
    }
    //guardar modificacion compra
    public function compras_guardar_modificacion(Request $request){
        $uuid=$request->uuid;
        $compra = $request->compra;
	    $ExisteUUID = Compra::where('Compra', '<>', $compra)->where('UUID', $uuid )->where('Status', '<>', 'BAJA')->first();
	    if($ExisteUUID == true){
	        $Compra = 1;
	    }else{  
            //obtener el ultimo id de la tabla
            $folio = Helpers::ultimofoliotablamodulos('App\Compra');
            //INGRESAR DATOS A TABLA COMPRAS
            $Compra = Compra::where('Compra', $compra)->first();
            $Compra->Proveedor=$request->numeroproveedor;
            $Compra->Movimiento="ALMACEN".$request->numeroalmacen;
            $Compra->Remision=$request->remision;
            $Compra->Factura=$request->factura;
            $Compra->UUID=$request->uuid;
            $Compra->Tipo=$request->tipo;
            $Compra->Plazo=$request->plazo;
            $Compra->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
            $Compra->Almacen=$request->numeroalmacen; 
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
            $Compra->Usuario=Auth::user()->user;
            $Compra->Periodo=$request->periodohoy;
            $Compra->save();
            //INGRESAR DATOS A TABLA ORDEN COMPRA DETALLES
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){             
                $CompraDetalle = CompraDetalle::where('Compra', $compra)->where('Item', $request->itempartida [$key])->first();
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
                $CompraDetalle->Depto = $request->departamentopartida [$key];
                $CompraDetalle->ClaveProducto = $request->claveproductopartida [$key];
                $CompraDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
                $CompraDetalle->save();
                //modificar fechaultimacompra y ultimocosto
                $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
                $Producto->{'Fecha Ultima Compra'} = Carbon::parse($request->fecha)->toDateTimeString();
                $Producto->{'Ultimo Costo'} = $request->preciopartida [$key];
                $Producto->save();
            }
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
        }    
            return response()->json($Compra); 

    }
    //verificar si la compra tiene relacion con alguna cuenta por pagar
    public function compras_verificar_uso_en_modulos(Request $request){
        $resultado = CuentaXPagarDetalle::where('Compra', $request->compradesactivar)->count();
        $numerocuentaxpagar = 0;
        //numero de detalles que no tienen existencias suficientes el almacen para cancelar
        $numerodetallesconexistenciasinsuficientes = 0;
        //verificar si hay una cuenta por pagar ligada
        if($resultado > 0){
            $detallecuentaxpagar = CuentaXPagarDetalle::where('Compra', $request->compradesactivar)->first();
            $numerocuentaxpagar = $detallecuentaxpagar->Pago;
        }else{
            //verificar si el almacen cuenta con las existencias
            $comprabaja = Compra::where('Compra', $request->compradesactivar)->first();
            $detallescomprabaja = CompraDetalle::where('Compra', $request->compradesactivar)->get();
            foreach($detallescomprabaja as $detallecomprabaja){
                $existencias = Existencia::select('Existencias')->where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->first();
                if($detallecomprabaja->Cantidad > $existencias->Existencias){
                    $numerodetallesconexistenciasinsuficientes++;
                }
            }
        }
        $data = array (
            'resultado' => $resultado,
            'numerocuentaxpagar' => $numerocuentaxpagar,
            'numerodetallesconexistenciasinsuficientes' => $numerodetallesconexistenciasinsuficientes
        );
        return response()->json($data);
    }
    //dar de baja compra
    public function compras_alta_o_baja(Request $request){
        //restar existencias de almacen y regresar cantidad inicial en el campo surtir de OrdenCompraDetalle
        $comprabaja = Compra::where('Compra', $request->compradesactivar)->first();
        $detallescomprabaja = CompraDetalle::where('Compra', $request->compradesactivar)->get();
        foreach($detallescomprabaja as $detallecomprabaja){
            //restar cantidades en existencias de almacen
            $existencias = Existencia::where('Codigo', $detallecomprabaja->Codigo)->where('Almacen', $comprabaja->Almacen)->first();
            $existencias->Existencias = $existencias->Existencias - $detallecomprabaja->Cantidad;
            $existencias->save();
            //regresar cantidad incial en campo surtir
            $ordencompradetalle = OrdenCompraDetalle::where('Codigo', $detallecomprabaja->Codigo)->where('Orden', $comprabaja->Orden)->first();
            $ordencompradetalle->Surtir = $ordencompradetalle->Surtir + $detallecomprabaja->Cantidad;
            $ordencompradetalle->save();

        }
        //colocar orden de compra en POR SURTIR
        $OrdenCompra = OrdenCompra::where('Orden', $comprabaja->Orden)->first();
        $OrdenCompra->Status = 'POR SURTIR';
        $OrdenCompra->save();
        //dar de baja compra
        $Compra = Compra::where('Compra', $request->compradesactivar)->first();
        $Compra->MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        $Compra->Status = 'BAJA';
        $Compra->save();
        return response()->json($Compra);
    }
    //ver movimientos de compra
    public function compras_obtener_movimientos_compra(Request $request){
        $movimientoscompra = CuentaXPagarDetalle::where('Compra', $request->compra)->get();
        $filasmovimientos = "";
        foreach($movimientoscompra as $mc){
            $CuentaXPagar = CuentaXPagar::where('Pago', $mc->Pago)->first();
            $filasmovimientos= $filasmovimientos.
            '<tr>'.
                '<td>CxP</td>'.
                '<td>'.$mc->Pago.'</td>'.
                '<td>'.$mc->Fecha.'</td>'.
                '<td>'.$mc->Abono.'</td>'.
                '<td>'.$CuentaXPagar->Status.'</td>'.
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
            $compras = Compra::whereIn('Compra', $request->arraypdf)->orderBy('Folio', 'ASC')->take(500)->get(); 
        }else{
            //$contrarecibos = ContraRecibo::where('Fecha', $request->anopdf)->get(); 
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $compras = Compra::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(500)->get();
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
                      //"retencioncompra"=>Helpers::convertirvalorcorrecto($c->IvaRetencion),
                      "totalcompra"=>Helpers::convertirvalorcorrecto($c->Total),
                      "proveedor" => $proveedor,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle
            );
        }
        //$footerHtml = view()->make('seccionespdf.footer', compact('fechaformato'))->render();
        $pdf = PDF::loadView('registros.compras.formato_pdf_compras', compact('data'))
        //->setOption('footer-html', $footerHtml, 'Página [page]')
        ->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-bottom', 10);
        //return $pdf->download('contrarecibos.pdf');
        return $pdf->stream();
    }

}
