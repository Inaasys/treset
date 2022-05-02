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
use App\Exports\PlantillasRemisionesExport;
use App\Imports\RemisionesImport;
use App\Exports\RemisionesExport;
use App\Remision;
use App\RemisionDetalle;
use App\CotizacionProducto;
use App\CotizacionProductoDetalle;
use App\Factura;
use App\FacturaDetalle;
use App\Serie;
use App\Almacen;
use App\Cliente;
use App\Agente;
use App\TipoCliente;
use App\TipoOrdenCompra;
use App\TipoUnidad;
use App\Existencia;
use App\BitacoraDocumento;
use App\Producto;
use App\Marca;
use App\Configuracion_Tabla;
use App\VistaRemision;
use App\VistaObtenerExistenciaProducto;
use App\Cotizacion;
use App\CotizacionDetalle;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Firma_Rel_Documento;
use App\ProductoPrecio;
use App\User_Rel_Almacen;
use Config;
use Mail;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use Storage; 
use ZipArchive;
use File;

class PuntoDeVentaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function punto_de_venta(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Remisiones', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('remisiones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('remisiones_exportar_excel');
        $rutacreardocumento = route('remisiones_generar_pdfs');
        $urlgenerarplantilla = route('remisiones_generar_plantilla');
        return view('registros.puntodeventa.puntodeventa', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','urlgenerarplantilla'));
    }

    public function punto_de_venta_obtener_producto_por_codigo(Request $request){
        $codigoabuscar = $request->codigoabuscar;
        $numeroalmacen = $request->numeroalmacen;
        //$contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'REFACCION')->where('Almacen', $numeroalmacen)->count();
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'REFACCION')->count();
        if($contarproductos > 0){
            //$producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'REFACCION')->where('Almacen', $numeroalmacen)->first();
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigoabuscar)->where('TipoProd', 'REFACCION')->first();
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

    public function punto_de_venta_obtener_datos_agregar_fila_producto(Request $request){
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $request->Codigo)->where('TipoProd', 'REFACCION')->count();
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $tipooperacion = $request->tipooperacion;
        $filasdetallesremision = '';
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $request->Codigo)->where('TipoProd', 'REFACCION')->first();
            $contarexistencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen', $request->numeroalmacen)->count();
            if($contarexistencia > 0){
                $Existencia = Existencia::where('Codigo', $request->Codigo)->where('Almacen', $request->numeroalmacen)->first();
                $Existencias = Helpers::convertirvalorcorrecto($Existencia->Existencias);
            }else{
                $Existencias = Helpers::convertirvalorcorrecto(0);
            }
            if($this->empresa->ColocarEnCeroCantidadEnPartidasDeRemisiones == 'S'){
                $cantidad = 0;
            }else{
                $cantidad = 1;
            }
            if($request->numerocliente > 0){
                $contarpreciocliente = ProductoPrecio::where('Codigo', $request->Codigo)->where('Cliente', $request->numerocliente)->count();
                if($contarpreciocliente > 0){
                    $precioproductocliente = ProductoPrecio::where('Codigo', $request->Codigo)->where('Cliente', $request->numerocliente)->first();
                    $preciopartida = $precioproductocliente->Precio;
                }else{
                    $preciopartida = $producto->Costo;
                }
            }else{
                $preciopartida = $producto->Costo;
            }
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
            $dataparsleyutilidad = "";
            if($this->validarutilidadnegativa == 'N'){
                if($cantidad > 0){
                    $dataparsleyutilidad = 'data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'"';
                }
            }
            $filasdetallesremision= $filasdetallesremision.
            '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                '<td class="tdmod hidden"><input type="text" class="form-control divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" data-parsley-length="[1, 20]"></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$producto->Codigo.'</b></td>'.
                '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet divorinputmodxl descripcionproductopartida" name="descripcionproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($producto->Producto, ENT_QUOTES).'</textarea></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$producto->Unidad.'</td>'.
                '<td class="tdmod" hidden>'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm pendientearemisionarpartida" name="pendientearemisionarpartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" onchange="formatocorrectoinputcantidades(this);">'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'"   data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.                         
                '</td>'.
                '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaop" name="preciopartidaop[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" ><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" '.$dataparsleyutilidad.' onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]" autocomplete="off"></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="" readonly data-parsley-length="[1, 20]"></td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                '<td class="tdmod" hidden><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="0" readonly></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
            '</tr>';
            $contadorproductos++;
            $contadorfilas++;
        }
        $data = array(
            "filasdetallesremision" => $filasdetallesremision,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data); 
    }

    public function punto_de_venta_obtener_nuevo_saldo_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        ///$nuevosaldo = $cliente->Saldo + $request->total;
        return response()->json(Helpers::convertirvalorcorrecto($cliente->Saldo));

    }

    public function punto_de_venta_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $credito = '';
        $saldo = '';
        $numeroagente = '';
        $nombreagente = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $agente = Agente::where('Numero', $cliente->Agente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
            $numeroagente = $agente->Numero;
            $nombreagente = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc,
            'credito' => $credito,
            'saldo' => $saldo,
            'numeroagente' => $numeroagente,
            'nombreagente' => $nombreagente
        );
        return response()->json($data);

    }
}
