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
use App\Exports\RemisionesDetallesExport;
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
use FastExcel;

class RemisionController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    public function remisiones(){
        $serieusuario = 'A';
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Remisiones', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('remisiones_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('remisiones_exportar_excel');
        $urlgenerarformatoexceldetalles = route('remision_detalles_exportar_excel');
        $rutacreardocumento = route('remisiones_generar_pdfs');
        $urlgenerarplantilla = route('remisiones_generar_plantilla');
        return view('registros.remisiones.remisiones', compact('serieusuario','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','urlgenerarformatoexceldetalles','rutacreardocumento','urlgenerarplantilla'));
    }

    //obtener registros tabla
    public function remisiones_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Remisiones', Auth::user()->id);
            $periodo = $request->periodo;
            $data = VistaRemision::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                        $operaciones =  '<div class="dropdown">'.
                                            '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                                'OPERACIONES <span class="caret"></span>'.
                                            '</button>'.
                                            '<ul class="dropdown-menu">'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Remision .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Remision .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('remisiones_generar_pdfs_indiv',$data->Remision).'" target="_blank">Ver Documento PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Remision .'\')">Enviar Documento por Correo</a></li>'.
                                                '<li class="operaciongenerarformatoreqtyt" hidden><a class="paddingmenuopciones" href="'.route('remisiones_generar_pdfs_indiv_requisicion_tyt',$data->Remision).'" target="_blank">Generar Formato Requisición TYT</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="modificardatosgeneralesdocumento(\''.$data->Remision .'\')">Modificar Datos Generales</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Remision .'\')">Imprimir Documento PDF</a></li>'.
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
                    ->addColumn('tipocambio', function($data){ return $data->TipoCambio; })
                    //->addColumn('supago', function($data){ return $data->SuPago; })
                    //->addColumn('enefectivo', function($data){ return $data->EnEfectivo; })
                    //->addColumn('entarjetas', function($data){ return $data->EnTarjetas; })
                    //->addColumn('envales', function($data){ return $data->EnVales; })
                    //->addColumn('encheque', function($data){ return $data->EnCheque; })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //descargar plantilla
    public function remisiones_generar_plantilla(){
        return Excel::download(new PlantillasRemisionesExport(), "plantillaremisiones.xlsx");
    }
    //cargar partidas excel
    public function remisiones_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new RemisionesImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesremision = '';
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
                    $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'REFACCION')->count();
                    if($contarproductos > 0){
                        $producto = VistaObtenerExistenciaProducto::where('Codigo', ''.$codigoabuscar.'')->where('TipoProd', 'REFACCION')->first();
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
                        if($request->numerocliente > 0){
                            $contarpreciocliente = ProductoPrecio::where('Codigo', ''.$codigoabuscar.'')->where('Cliente', $request->numerocliente)->count();
                            if($contarpreciocliente > 0){
                                $precioproductocliente = ProductoPrecio::where('Codigo', ''.$codigoabuscar.'')->where('Cliente', $request->numerocliente)->first();
                                $preciopartida = $precioproductocliente->Precio;
                            }else{
                                $preciopartida = $partida[2];
                            }
                        }else{
                            //dd(Helpers::convertirvalorcorrecto($cantidad));
                            //precio de la partida
                            //$preciopartida = $producto->SubTotal;
                            if(Helpers::convertirvalorcorrecto($partida[2]) > 0){
                                $preciopartida = $partida[2];
                            }else{
                                $preciopartida = $producto->SubTotal;
                            }
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
                            '<td class="tdmod tdinsumospartidas"><input type="text" class="form-control inputnextdet divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'"  data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$producto->Codigo.'</b></td>'.
                            '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet descripcionproductopartida" name="descripcionproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($producto->Producto, ENT_QUOTES).'</textarea></td>'.
                            '<td class="tmod">'.
                                '<input class="form-control inputnextdet existenciaspartida" value="'. number_format($Existencias,$this->numerodecimales,'.',',').'">'.
                            '</td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$producto->Unidad.'</td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pendientearemisionarpartida" name="pendientearemisionarpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" onchange="formatocorrectoinputcantidades(this);">'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaop" name="preciopartidaop[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" >'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($preciopartida),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($importepartida),$this->numerodecimales,'.',',').'" readonly>'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto(0),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($subtotalpartida),$this->numerodecimales,'.',',').'"readonly>'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm ivapesospartidaAux" name="ivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($ivapesospartida),$this->numerodecimales,'.',',').'" readonly>'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod">'.
                                '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($totalpesospartida),$this->numerodecimales, '.',',').'" readonly>'.
                                '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" '.$dataparsleyutilidad.' onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="" readonly data-parsley-length="[1, 20]"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="0" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
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
            "filasdetallesremision" => $filasdetallesremision,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
        );
        return response()->json($data);
    }
    //obtener series documento
    public function remisiones_obtener_series_documento(Request $request){
        if($request->ajax()){
            $data = Serie::where('Documento', 'Remisiones')->where('Usuario', Auth::user()->user)->get();
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
    public function remisiones_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->Serie);
        return response()->json($folio);
    }
    //obtener ultimo folio
    public function remisiones_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->serie);
        return response()->json($folio);
    }

    //obtener clientes
    public function remisiones_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = DB::table('Clientes as c')
            ->leftJoin('Agentes as a', 'a.Numero', '=', 'c.Agente')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'a.Numero AS NumeroAgente', 'a.Nombre AS NombreAgente')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "ASC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\',\''.$data->Rfc .'\',\''.Helpers::convertirvalorcorrecto($data->Credito).'\',\''.Helpers::convertirvalorcorrecto($data->Saldo).'\',\''.$data->NumeroAgente .'\',\''.$data->NombreAgente .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente por numero
    public function remisiones_obtener_cliente_por_numero(Request $request){
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

    //obtener agente por numero
    public function remisiones_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeagente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener almacenes
    public function remisiones_obtener_almacenes(Request $request){
        if($request->ajax()){
            $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
            if($contaralmacenesasignadosausuario > 0){
                $data = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'ASC')
                ->get();
            }else{
                $data = Almacen::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            }
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
    public function remisiones_obtener_almacen_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $plazo = '';
        $contaralmacenesasignadosausuario = User_Rel_Almacen::where('user_id', Auth::user()->id)->count();
        if($contaralmacenesasignadosausuario > 0){
            $existealmacen = DB::table('user_rel_almacenes as ura')
            ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
            ->select('ura.id', 'a.Numero', 'a.Nombre')
            ->where('a.Numero', $request->numeroalmacen)
            ->where('a.Status', 'ALTA')
            ->where('ura.user_id', Auth::user()->id)
            ->count();
            if($existealmacen > 0){
                $almacen = DB::table('user_rel_almacenes as ura')
                ->join('Almacenes as a', 'ura.almacen_id', '=', 'a.Numero')
                ->select('ura.id', 'a.Numero', 'a.Nombre')
                ->where('a.Numero', $request->numeroalmacen)
                ->where('a.Status', 'ALTA')
                ->where('ura.user_id', Auth::user()->id)
                ->orderby('a.Numero', 'DESC')
                ->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }else{
            $existealmacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->count();
            if($existealmacen > 0){
                $almacen = Almacen::where('Numero', $request->numeroalmacen)->where('Status', 'ALTA')->first();
                $numero = $almacen->Numero;
                $nombre = $almacen->Nombre;
            }
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }

    //obtener tipos cliente
    public function remisiones_obtener_tipos_cliente(){
        $tipos_cliente = TipoOrdenCompra::where('STATUS', 'ALTA')->where('Nombre', '<>', 'GASTOS')->Where('Nombre', '<>', 'TOT')->get();
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

    //obtener cotizaciones
    public function remisiones_obtener_cotizaciones(Request $request){
        if($request->ajax()){
            $mesactual = date("m");
            $data = DB::table('Cotizaciones as cot')
                        ->join('Clientes as c', 'c.Numero', '=', 'cot.Cliente')
                        ->select('cot.Cotizacion', 'cot.Folio', 'cot.Fecha', 'cot.Cliente', 'c.Nombre as Nombre', 'cot.Tipo', 'cot.Plazo as Dias', 'cot.Total')
                        ->where('cot.Cliente', $request->numerocliente)
                        ->where('cot.Status', 'POR CARGAR')
                        ->whereMonth('cot.Fecha', '=', $mesactual)
                        ->orderBy("Folio", "DESC")
                        ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcotizacion('.$data->Folio.',\''.$data->Cotizacion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){ return Helpers::fecha_espanol($data->Fecha);  })
                    ->addColumn('Total', function($data){ return Helpers::convertirvalorcorrecto($data->Total);  })

                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener datos de la cotizaciones seleccionada
    public function remisiones_obtener_cotizacion(Request $request){
        $cotizacion = CotizacionProducto::where('Cotizacion', $request->Cotizacion)->first();
        $almacen = $request->numeroalmacen;
        //detalles cotizacion
        $detallescotizacion = CotizacionProductoDetalle::where('Cotizacion', $request->Cotizacion)->get();
        $numerodetallescotizacion = CotizacionProductoDetalle::where('Cotizacion', $request->Cotizacion)->count();
        if($numerodetallescotizacion > 0){
            $filasdetallescotizacion = '';
            $contadorproductos = 0;
            $contadorfilas = 0;
            $tipo = "alta";
            foreach($detallescotizacion as $dc){
                $contarsiexisteproducto = Producto::where('Codigo', $dc->Codigo)->count();
                if($contarsiexisteproducto > 0){
                    $producto = Producto::where('Codigo', $dc->Codigo)->first();
                    $contarexistencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', $almacen)->count();
                    if($contarexistencia > 0){
                        $Existencia = Existencia::where('Codigo', $dc->Codigo)->where('Almacen', $almacen)->first();
                        $parsleymax = $Existencia->Existencias;
                    }else{
                        $parsleymax = 0;
                    }
                    $dataparsleyutilidad = "";
                    if($this->validarutilidadnegativa == 'N'){
                        if($dc->Cantidad > 0){
                            $dataparsleyutilidad = 'data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'"';
                        }
                    }
                    $filasdetallescotizacion= $filasdetallescotizacion.
                    '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipo.'" readonly></td>'.
                        '<td class="tdmod tdinsumospartidas"><input type="text" class="form-control inputnextdet divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dc->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$dc->Codigo.'</b></td>'.
                        '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet descripcionproductopartida" name="descripcionproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($dc->Descripcion, ENT_QUOTES).'</textarea></td>'.
                        '<td class="tmod"><input type="number" class="form-control inputnextdet divorinputmodsm insumopartida" value="'.number_format($Existencia->Existencias,$this->numerodecimales,'.',',').'" disabled /></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dc->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dc->Unidad.'</td>'.
                        '<td class="tdmod">'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pendientearemisionarpartida" name="pendientearemisionarpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" onchange="formatocorrectoinputcantidades(this);">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                            '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                            '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaop" name="preciopartidaop[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" >'.
                            '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Precio),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Descuento),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm ivapesospartidaAux" name="ivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Total),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                        '<td class="tdmod">'.
                            '<input type="text" class="form-control divorinputmodsm utilidadpartidaAux" name="utilidadpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dc->Utilidad),$this->numerodecimales,'.',',').'" readonly>'.
                            '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dc->Utilidad).'" '.$dataparsleyutilidad.' onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '</td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dc->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dc->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dc->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$dc->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                        '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                        '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                        '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMeses).'" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresTasa).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto($dc->InteresMonto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '</tr>';
                    $contadorproductos++;
                    $contadorfilas++;
                }
            }
        }else{
            $filasdetallescotizacion = '';
        }
        $data = array(
            "cotizacion" => $cotizacion,
            "filasdetallescotizacion" => $filasdetallescotizacion,
            "numerodetallescotizacion" => $numerodetallescotizacion,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "almacen" => $almacen,
            "fecha" => Helpers::formatoinputdatetime($cotizacion->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($cotizacion->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($cotizacion->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($cotizacion->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($cotizacion->Iva),
            "total" => Helpers::convertirvalorcorrecto($cotizacion->Total)
        );
        return response()->json($data);
    }

    //obtener nuevo saldo cliente
    public function remisiones_obtener_nuevo_saldo_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        ///$nuevosaldo = $cliente->Saldo + $request->total;
        return response()->json(Helpers::convertirvalorcorrecto($cliente->Saldo));
    }

    //obtener prudoctos
    public function remisiones_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $numeroalmacen = $request->numeroalmacen;
            $tipooperacion = $request->tipooperacion;
            //$data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'REFACCION')->where('Almacen', $numeroalmacen);
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%')->where('TipoProd', 'REFACCION');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion, $numeroalmacen){
                        if($data->Almacen == $numeroalmacen){
                            //$boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.Helpers::convertirvalorcorrecto($data->Existencias).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
                            $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="obtenerdatosagregarfilaproducto(\''.$data->Codigo .'\')">Seleccionar</div>';
                        }else{
                            $boton = '';
                        }
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
    public function remisiones_obtener_producto_por_codigo(Request $request){
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

    //obtener datos agregar filas
    public function remisiones_obtener_datos_agregar_fila_producto(Request $request){
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
                '<td class="tdmod tdinsumospartidas"><input type="text" class="form-control inputnextdet divorinputmodsm insumopartida" name="insumopartida[]" value="'.$producto->Insumo.'" data-parsley-length="[1, 20]"></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$producto->Codigo.'</b></td>'.
                '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet descripcionproductopartida" name="descripcionproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($producto->Producto, ENT_QUOTES).'</textarea></td>'.
                '<td class="tmod"><input type="number" class="form-control inputnextdet divorinputmodsm insumopartida" value="'.$Existencias.'" disabled /></td>'.
                '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$producto->Unidad.'" readonly data-parsley-length="[1, 5]" onkeyup="tipoLetra(this)">'.$producto->Unidad.'</td>'.
                '<td class="tdmod">'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pendientearemisionarpartida" name="pendientearemisionarpartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" onchange="formatocorrectoinputcantidades(this);">'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'"   data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaop" name="preciopartidaop[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" >'.
                    '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($preciopartida),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($importepartida),$this->numerodecimales,'.',',').'" readonly>'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');">'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto(0),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($subtotalpartida),$this->numerodecimales,'.',',').'" readonly>'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                '</td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control divorinputmodsm ivapesospartidaAux" name="ivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($ivapesospartida),$this->numerodecimales,'.',',').'" readonly>'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($ivapesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                '</td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($totalpesospartida),$this->numerodecimales,'.',',').'" readonly>'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                '</td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                '<td class="tdmod">'.
                    '<input type="text" class="form-control divorinputmodsm utilidadpartidaAux" name="utilidadpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($utilidadpartida),$this->numerodecimales,'.',',').'" readonly>'.
                    '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" '.$dataparsleyutilidad.' onchange="formatocorrectoinputcantidades(this);" readonly>'.
                '</td>'.
                '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly data-parsley-length="[1, 3]" autocomplete="off"></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="" readonly data-parsley-length="[1, 20]"></td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
                '<td class="tdmod"><input type="text" class="form-control divorinputmodsm mesespartida" name="mesespartida[]" value="0" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tasainterespartida" name="tasainterespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm montointerespartida" name="montointerespartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
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

    //verificar si las partidas de la remision ya estan en una OT
    public function remisiones_revisar_insumos_orden_trabajo_por_folio(Request $request){
        $insumos = "";
        $numeroinsumosenorden=0;
        foreach($request->codigos as $cod){
            $insumoenorden = OrdenTrabajoDetalle::where('Orden', $request->orden)->where('Codigo', $cod)->count();
            if($insumoenorden > 0){
                $insumos = $insumos."Aviso la Orden No. ".$request->orden." ya tiene cargado el insumo: ".$cod."<br>";
                $numeroinsumosenorden++;
            }
        }
        $data = array(
            'insumos' => $insumos,
            'numeroinsumosenorden' => $numeroinsumosenorden
        );
        return response()->json($data);
    }

    //obtener series de requisiciones
    public function remisiones_obtener_series_requisiciones(Request $request){
        if($request->ajax()){
            $data = Remision::select('SerieRq')->where('SerieRq', '<>', '')->groupby('SerieRq')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarserierq(\''.$data->SerieRq.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener ultimo numero de serie de requisicon seleccionada
    public function remisiones_obtener_ultimo_numero_serierq_seleccionada(Request $request){
        // if($request->$request->SerieRq == 'C' || $request->$request->SerieRq == 'L'){
        //     $ultimonumero = Remision::select("Rq")
        //     ->where('SerieRq', $request->$request->SerieRq)
        //     ->where('Rq','>',9999)
        //     ->orderBy("Rq", "DESC")->take(1)->get();
        // }else{
        //     $ultimonumero = Remision::select("Rq")
        //     ->where('SerieRq', $request->$request->SerieRq)
        //     ->orderBy("Rq", "DESC")->take(1)->get();
        // }
        $ultimonumero = Remision::select("Rq")->where('SerieRq', $request->SerieRq)->orderBy("Rq", "DESC")->take(1)->get();
        if(sizeof($ultimonumero) == 0 || sizeof($ultimonumero) == "" || sizeof($ultimonumero) == null){
            $numerorequisicion = 1;
        }else{
            $numerorequisicion = $ultimonumero[0]->Rq+1;
        }
        return response()->json($numerorequisicion);
    }

    //obtener serie rq por serie
    public function remisiones_obtener_serierq_por_serie(Request $request){

        // if($request->serierequisicion == 'C' || $request->serierequisicion == 'L'){
        //     $ultimonumero = Remision::select("Rq")
        //     ->where('SerieRq', $request->serierequisicion)
        //     ->where('Rq','>',9999)
        //     ->orderBy("Rq", "DESC")->take(1)->get();
        // }else{
        //     $ultimonumero = Remision::select("Rq")
        //     ->where('SerieRq', $request->serierequisicion)
        //     ->orderBy("Rq", "DESC")->take(1)->get();
        // }
        // if(sizeof($ultimonumero) == 0 || sizeof($ultimonumero) == "" || sizeof($ultimonumero) == null){
        //     $numerorequisicion = 1;
        // }else{
        //     $numerorequisicion = $ultimonumero[0]->Rq+1;
        // }

        $ultimonumero = Remision::select("Rq")->where('SerieRq', $request->serierequisicion)->orderBy("Rq", "DESC")->take(1)->get();
        if(sizeof($ultimonumero) == 0 || sizeof($ultimonumero) == "" || sizeof($ultimonumero) == null){
            $numerorequisicion = 1;
        }else{
            $numerorequisicion = $ultimonumero[0]->Rq+1;
        }
        return response()->json($numerorequisicion);

    }

    //guardar
    public function remisiones_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //revisar si hay existencias suficientes para guardar la remision
        $arraypartidassinexistencias = Array();
        $detallescodigossinexistencias = 0;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
            $numeroalmacen = $request->numeroalmacen;
            $cantidadpartida = $request->cantidadpartida [$key];
            //$cantidadpartidadb = $request->cantidadpartidadb [$key];
            $ContarExistencias = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $numeroalmacen)->count();
            if($ContarExistencias > 0){
                $Existencias = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $numeroalmacen)->first();
                $ExistenciasTabla = $Existencias->Existencias;
            }else{
                $ExistenciasTabla = 0;
            }
            $ExistenciasActualesMasExistenciasCaptura = $ExistenciasTabla;
            $ExistenciasNueva = $ExistenciasActualesMasExistenciasCaptura - $cantidadpartida;
            $data = array(
                    "Codigo" => $codigoproductopartida,
                    "ExistenciasActualesMasExistenciasCaptura" => Helpers::convertirvalorcorrecto($ExistenciasActualesMasExistenciasCaptura),
                    "ExistenciasARestarEnModificacion" => Helpers::convertirvalorcorrecto($cantidadpartida),
                    "ExistenciasNueva" => Helpers::convertirvalorcorrecto($ExistenciasNueva)
            );
            array_push($arraypartidassinexistencias, $data);
            if($ExistenciasNueva < 0){
                $detallescodigossinexistencias++;
            }
        }
        $dataexistencias = array(
            "arraypartidassinexistencias" =>  $arraypartidassinexistencias,
            "detallescodigossinexistencias" => $detallescodigossinexistencias
        );
        if($detallescodigossinexistencias != ""){
            return response()->json($dataexistencias);
        }else{
            if($this->modificarconsecutivofolioenremisiones == 'S'){
                $folio = $request->folio;
            }else{
                //obtener el ultimo folio de la tabla
                $folio = Helpers::ultimofolioserietablamodulos('App\Remision',$request->serie);
            }
            //INGRESAR DATOS A TABLA ORDEN COMPRA
            $remision = $folio.'-'.$request->serie;
            $ExisteRemision = Remision::where('Remision', $remision)->first();
            if($ExisteRemision == true){
                $Remision = 1;
            }else{
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
                $Remision->SerieRq=$request->serierequisicion;
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
                $Remision->Periodo=$this->periodohoy;
                $Remision->save();
                //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
                $BitacoraDocumento = new BitacoraDocumento;
                $BitacoraDocumento->Documento = "REMISIONES";
                $BitacoraDocumento->Movimiento = $remision;
                $BitacoraDocumento->Aplicacion = "ALTA";
                $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
                $BitacoraDocumento->Status = "POR FACTURAR";
                $BitacoraDocumento->Usuario = Auth::user()->user;
                $BitacoraDocumento->Periodo = $this->periodohoy;
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
                    $RemisionDetalle->TipoDeCambio =  $request->tipocambiopartida [$key];
                    $RemisionDetalle->Cotizacion =  $request->cotizacionpartida [$key];
                    $RemisionDetalle->Insumo =  $request->insumopartida [$key];
                    $RemisionDetalle->InteresMeses =  $request->mesespartida [$key];
                    $RemisionDetalle->InteresTasa =  $request->tasainterespartida  [$key];
                    $RemisionDetalle->InteresMonto =  $request->montointerespartida  [$key];
                    $RemisionDetalle->PorRemisionar = $request->pendientearemisionarpartida [$key];
                    $RemisionDetalle->Item = $item;
                    $RemisionDetalle->save();
                    //modificar fechaultimaventa y ultimocosto
                    /*
                    $Producto = Producto::where('Codigo', $codigoproductopartida)->first();
                    $Producto->{'Fecha Ultima Venta'} = Carbon::parse($request->fecha)->toDateTimeString();
                    $Producto->{'Ultima Venta'} = $request->preciopartida [$key];
                    $Producto->save();
                    */
                    Producto::where('Codigo', $codigoproductopartida)
                    ->update([
                        'Fecha Ultima Venta' => Carbon::parse($request->fecha)->toDateTimeString(),
                        'Ultima Venta' => $request->preciopartida [$key]
                    ]);
                    if($request->cantidadpartida [$key] > 0){
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
                    $item++;
                }
            }
            return response()->json($Remision);
        }
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
        $ContarRemisionCotizados = CotizacionDetalle::where('num_remision', $request->remisiondesactivar)->count();
        if($ContarRemisionCotizados > 0){
            $RemisionCotizados = CotizacionDetalle::where('num_remision', $request->remisiondesactivar)->get();
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
            //si se utilizo cotizacion regresar a alta el status
            if($detalle->Cotizacion != ''){
                CotizacionProducto::where('Cotizacion', $detalle->Cotizacion)
                        ->update([
                            'Status' => 'POR CARGAR'
                        ]);
            }
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
                                'Utilidad' => '0.000000',
                                'Cotizacion' => '',
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
                $ContarExistenciaAlmacen = Existencia::where('Codigo', $dr->Codigo)->where('Almacen', $remision->Almacen)->count();
                if($ContarExistenciaAlmacen > 0){
                    $Existencia = Existencia::where('Codigo', $dr->Codigo)->where('Almacen', $remision->Almacen)->first();
                    $parsleymax = $Existencia->Existencias+$dr->Cantidad;
                }else{
                    $parsleymax = $dr->Cantidad;
                }
                $dataparsleyutilidad = "";
                if($this->validarutilidadnegativa == 'N'){
                    if($dr->Cantidad > 0){
                        $dataparsleyutilidad = 'data-parsley-utilidad="0.'.$this->numerocerosconfiguradosinputnumberstep.'"';
                    }
                }
                //dd($dataparsleyutilidad);
                $filasdetallesremision= $filasdetallesremision.
                '<tr class="filasproductos" id="filaproducto'.$contadorproductos.'">'.
                    '<td class="tdmod"><div class="btn btn-danger btn-xs" onclick="eliminarfila('.$contadorproductos.')">X</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$dr->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod tdinsumospartidas"><input type="text" class="form-control inputnextdet divorinputmodsm insumopartida" name="insumopartida[]" value="'.$dr->Insumo.'" data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigoproductopartida" name="codigoproductopartida[]" value="'.$dr->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;" class="codigopartidatexto">'.$dr->Codigo.'</b></td>'.
                    '<td class="tdmod"><textarea rows="1" class="form-control inputnextdet descripcionproductopartida" name="descripcionproductopartida[]" required data-parsley-length="[1, 255]" onkeyup="tipoLetra(this)" autocomplete="off" style="font-size:10px;">'.htmlspecialchars($dr->Descripcion, ENT_QUOTES).'</textarea></td>'.
                    '<td class="tmod"><input type="number" class="form-control inputnextdet divorinputmodsm insumopartida" value="'.$parsleymax.'" disabled /></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control unidadproductopartida" name="unidadproductopartida[]" value="'.$dr->Unidad.'" readonly data-parsley-length="[1, 5]">'.$dr->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm pendientearemisionarpartida" name="pendientearemisionarpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->PorRemisionar).'" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodecantidadpartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartidaop" name="preciopartidaop[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" >'.
                        '<input type="text" class="form-control inputnextdet divorinputmodsm preciopartidaAux" name="preciopartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Precio),$this->numerodecimales,'.',',').'" onchange="formatocorrectoinputcantidadesComma(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Precio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');cambiodepreciopartida('.$contadorfilas.',\''.$tipo.'\');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodsm importepartidaAux" name="importepartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Importe),$this->numerodecimales,'.',',').'" readonly>'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control inputnextdet divorinputmodsm descuentopesospartidaAux" name="descuentopesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Descuento),$this->numerodecimales,'.',',').'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidadesComma(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');">'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodsm subtotalpartidaAux" name="subtotalpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->SubTotal),$this->numerodecimales,'.',',').'" readonly>'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodsm ivapesospartidaAux" name="ivapesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Iva),$this->numerodecimales,'.',',').'" readonly>'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivapesospartida" name="ivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '</td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodsm totalpesospartidaAux" name="totalpesospartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Total),$this->numerodecimales,'.',',').'" readonly>'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionespesospartida" name="comisionespesospartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod">'.
                        '<input type="text" class="form-control divorinputmodsm utilidadpartidaAux" name="utilidadpartidaAux[]" value="'.number_format(Helpers::convertirvalorcorrecto($dr->Utilidad),$this->numerodecimales,'.',',').'" readonly>'.
                        '<input type="number" style="display:none;" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($dr->Utilidad).'" '.$dataparsleyutilidad.' onchange="formatocorrectoinputcantidades(this);" readonly>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$dr->Moneda.'" readonly data-parsley-length="[1, 3]"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costolistapartida" name="costolistapartida[]" value="'.Helpers::convertirvalorcorrecto($dr->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($dr->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cotizacionpartida" name="cotizacionpartida[]" value="'.$dr->Cotizacion.'" readonly data-parsley-length="[1, 20]"></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveprodutopartida" name="claveprodutopartida[]" value="'.$producto->ClaveProducto.'" readonly required></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]" value="'.$producto->ClaveUnidad.'" readonly required></td>'.
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

                //Valida que la remision no tenga mas de dos meses que fue levantada
                // $resultadofechas = Helpers::diasModificacionRemisiones($remision->Fecha);
                // if(((int)$resultadofechas->y == 0) && ((int)$resultadofechas->m <= 1) && ((int)$resultadofechas->d <=30 )){
                //     $modificacionpermitida = 1;
                // }else{
                //     $modificacionpermitida = 0;
                // }
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
            "fecha" => Helpers::formatoinputdatetime($remision->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($remision->Fecha),
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
        ini_set('max_input_vars','20000' );
        //revisar si hay existencias suficientes para guardar la remision
        $arraypartidassinexistencias = Array();
        $detallescodigossinexistencias = 0;
        foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
            $numeroalmacen = $request->numeroalmacen;
            $cantidadpartida = $request->cantidadpartida [$key];
            $agregadoen = $request->agregadoen [$key];
            if($agregadoen == "modificacion"){
                $cantidadpartidadb = 0;
            }else{
                $cantidadpartidadb = $request->cantidadpartidadb [$key];
            }
            $ContarExistencias = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $numeroalmacen)->count();
            if($ContarExistencias > 0){
                $Existencias = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $numeroalmacen)->first();
                $ExistenciasTabla = $Existencias->Existencias;
            }else{
                $ExistenciasTabla = 0;
            }
            $Existencias = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $numeroalmacen)->first();
            $ExistenciasActualesMasExistenciasCaptura = $ExistenciasTabla + $cantidadpartidadb;
            $ExistenciasNueva = $ExistenciasActualesMasExistenciasCaptura - $cantidadpartida;
            $data = array(
                    "Codigo" => $codigoproductopartida,
                    "ExistenciasActualesMasExistenciasCaptura" => Helpers::convertirvalorcorrecto($ExistenciasActualesMasExistenciasCaptura),
                    "ExistenciasARestarEnModificacion" => Helpers::convertirvalorcorrecto($cantidadpartida),
                    "ExistenciasNueva" => Helpers::convertirvalorcorrecto($ExistenciasNueva)
            );
            array_push($arraypartidassinexistencias, $data);
            if($ExistenciasNueva < 0){
                $detallescodigossinexistencias++;
            }
        }
        $dataexistencias = array(
            "arraypartidassinexistencias" =>  $arraypartidassinexistencias,
            "detallescodigossinexistencias" => $detallescodigossinexistencias
        );
        if($detallescodigossinexistencias != ""){
            return response()->json($dataexistencias);
        }else{
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
                'SerieRq' => $request->serierequisicion,
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
            $BitacoraDocumento->Periodo = $this->periodohoy;
            $BitacoraDocumento->save();
            //INGRESAR DATOS A TABLA DETALLES
            foreach ($request->codigoproductopartida as $key => $codigoproductopartida){
                //if la partida se agrego en la modificacion se agrega en los detalles de traspaso y de orden de trabajo si asi lo requiere
                if($request->agregadoen [$key] == 'modificacion'){
                    $contaritems = RemisionDetalle::select('Item')->where('Remision', $remision)->count();
                    if($contaritems > 0){
                        $item = RemisionDetalle::select('Item')->where('Remision', $remision)->orderBy('Item', 'DESC')->take(1)->get();
                        $ultimoitem = $item[0]->Item+1;
                    }else{
                        $ultimoitem = 1;
                    }
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
                    //solo si el usuario esta autorizado en modificar el dato insumo
                    if (in_array(strtoupper(Auth::user()->user), explode(",",$this->usuariosamodificarinsumos))) {
                        RemisionDetalle::where('Remision', $remision)
                        ->where('Item', $request->itempartida [$key])
                                ->update([
                                    'Insumo'=>$request->insumopartida [$key],
                                ]);
                    }
                    $ContarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->count();
                    if($ContarExistenciaAlmacen > 0){
                        //sumar existencias a almacen principal
                        $SumarExistenciaAlmacen = Existencia::where('Codigo', $codigoproductopartida)->where('Almacen', $request->numeroalmacen)->first();
                        $SumarExistenciaNuevaAlmacen = $SumarExistenciaAlmacen->Existencias + $request->cantidadpartidadb [$key];
                        Existencia::where('Codigo', $codigoproductopartida)
                                    ->where('Almacen', $request->numeroalmacen)
                                    ->update([
                                        'Existencias' => $SumarExistenciaNuevaAlmacen
                                    ]);
                    }
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
    }

    //obtener datos generales
    public function remisiones_obtener_datos_generales(Request $request){
        $documento = Remision::where('Remision', $request->Remision)->first();
        return response()->json($documento);
    }

    //guardar cambios datos generales
    public function remisiones_guardar_modificacion_datos_generales(Request $request){
        $Remision = Remision::where('Remision', $request->remisiondatosgenerales)->first();
        Remision::where('Remision', $request->remisiondatosgenerales)
        ->update([
            'Os'=>$request->ordenserviciodatosgenerales,
            'Eq'=>$request->equipodatosgenerales,
        ]);
        return response()->json($Remision);
    }

    //buscar folio
    public function remisiones_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaRemision::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        }
    }

    //generar documento pdf
    public function remisiones_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $remisiones = Remision::where('Remision', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $remisiones = Remision::whereIn('Remision', $request->arraypdf)->orderBy('Folio', 'ASC')->take(1500)->get();
            }else{
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $remisiones = Remision::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(1500)->get();
                }else{
                    $remisiones = Remision::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(1500)->get();
                }
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfilespdf = array();
        foreach ($remisiones as $r){
            $data=array();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Remisiones')->where('Documento', $r->Remision)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Remisiones')
            ->where('frd.Documento', $r->Remision)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "remision"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
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
            //dd($data);
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$r->Remision.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($remisiones as $re){
            $ArchivoPDF = "PDF".$re->Remision.".pdf";
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
                $pdfMerger->save("Remisiones.pdf", "browser");//mostrarlos en el navegador
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
    public function remisiones_generar_pdfs_indiv($documento){
        $remisiones = Remision::where('Remision', $documento)->get();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Remisiones')->where('Documento', $r->Remision)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Remisiones')
            ->where('frd.Documento', $r->Remision)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "remision"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
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

    //generar formato pdf requisicion TYT
    public function remisiones_generar_pdfs_indiv_requisicion_tyt($documento){
        $remisiones = Remision::where('Remision', $documento)->get();
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
                    "insumodetalle"=>$producto->Insumo,
                    "descripciondetalle"=>$rd->Descripcion
                );
            }
            $data[]=array(
                      "remision"=>$r,
                      "fechaformato"=> $fechaformato,
                      "dia" => Carbon::parse($r->Fecha)->format('d'),
                      "mes" => Carbon::parse($r->Fecha)->format('m'),
                      "anio" => Carbon::parse($r->Fecha)->format('Y'),
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_requisicion_tyt', compact('data'))
        ->setPaper('Letter')
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //generar formato req tyt desde modificacion
    public function remisiones_generar_formato_req_tyt_en_modificacion_remision(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $remisiones = $request->arraycodigosformatoreqtyt;
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
            $remisiondetalle = $request->arraycodigosformatoreqtyt;
            $datadetalle=array();
            foreach($remisiondetalle as $rd){
                $datadetalle[]=array(
                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($rd[3]),
                    "codigodetalle"=>$rd[1],
                    "insumodetalle"=>$rd[0],
                    "descripciondetalle"=>$rd[2]
                );
            }
            $fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $data[]=array(
                      "referencia"=>$request->referencia,
                      "ordenservicio"=>$request->ordenservicio,
                      "equipo"=>$request->equipo,
                      "fechaformato"=> $fechaformato,
                      "dia" => Carbon::parse($request->fecha)->format('d'),
                      "mes" => Carbon::parse($request->fecha)->format('m'),
                      "anio" => Carbon::parse($request->fecha)->format('Y'),
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_requisicion_tyt_en_mod', compact('data'))
        ->setPaper('Letter')
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        $ArchivoPDF = "PDFREQTYT".$request->referencia."-".$request->ordenservicio.".pdf";
        $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        $archivoacopiar = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
        $carpetacopias = public_path('xml_descargados/'.$ArchivoPDF);
        File::copy($archivoacopiar, $carpetacopias);
        return response()->json($ArchivoPDF);
    }

    //obtener datos para enviar email
    public function remisiones_obtener_datos_envio_email(Request $request){
        $remision = Remision::where('Remision', $request->documento)->first();
        $cliente = Cliente::where('Numero',$remision->Cliente)->first();
        $email2cc = '';
        $email3cc = '';
        if($cliente->Email2 != '' || $cliente->Email2 != null){
            $email2cc = $cliente->Email2;
        }
        if($cliente->Email3 != '' || $cliente->Email3 != null){
            $email3cc = $cliente->Email3;
        }
        $data = array(
            'remision' => $remision,
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
    public function remisiones_enviar_pdfs_email(Request $request){
        $remisiones = Remision::where('Remision', $request->emaildocumento)->get();
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
            //obtener firmas
            $numerofirmas = Firma_Rel_Documento::where('TipoDocumento', 'Remisiones')->where('Documento', $r->Remision)->where('Status', 'ALTA')->count();
            $firmas = DB::table('firmas_rel_documentos as frd')
            ->select("u.name", "frd.Fecha", "frd.ReferenciaPosicion", "frd.TipoDocumento", "frd.Documento", "frd.Status")
            ->leftjoin('users as u', 'frd.IdUsuario', '=', 'u.id')
            ->where('frd.TipoDocumento', 'Remisiones')
            ->where('frd.Documento', $r->Remision)
            ->where('frd.Status', 'ALTA')
            ->get();
            $data[]=array(
                      "remision"=>$r,
                      "numerofirmas"=>$numerofirmas,
                      "firmas"=>$firmas,
                      "descuentoremision"=>Helpers::convertirvalorcorrecto($r->Descuento),
                      "subtotalremision"=>Helpers::convertirvalorcorrecto($r->SubTotal),
                      "ivaremision"=>Helpers::convertirvalorcorrecto($r->Iva),
                      "totalremision"=>Helpers::convertirvalorcorrecto($r->Total),
                      "cliente" => $cliente,
                      "agente" => $agente,
                      "fechaformato"=> $fechaformato,
                      "datadetalle" => $datadetalle,
                      "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.remisiones.formato_pdf_remisiones', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        try{
            $datosdocumento = Remision::where('Remision', $request->emaildocumento)->first();
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
                        ->attachData($pdf->output(), "RemisionNo".$emaildocumento.".pdf");
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
    public function remisiones_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Remisiones', Auth::user()->id);
        return Excel::download(new RemisionesExport($configuraciones_tabla['campos_consulta'],$request->periodo), "remisiones-".$request->periodo.".xlsx");
    }
    public function remision_detalles_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $campos_consulta = [];
        array_push($campos_consulta, 'Remision');
        array_push($campos_consulta, 'Fecha');
        array_push($campos_consulta, 'Codigo');
        array_push($campos_consulta, 'Descripcion');
        array_push($campos_consulta, 'Unidad');
        array_push($campos_consulta, 'Cantidad');
        array_push($campos_consulta, 'Precio');
        array_push($campos_consulta, 'Importe');
        array_push($campos_consulta, 'SubTotal');
        array_push($campos_consulta, 'Iva');
        array_push($campos_consulta, 'Total');
        array_push($campos_consulta, 'Costo');
        array_push($campos_consulta, 'CostoTotal');
        return Excel::download(new RemisionesDetallesExport($campos_consulta,$request->remision), "remision-".$request->remision.".xlsx");
        }
    //guardar configuracion tabla
    public function remisiones_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Remisiones', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Remisiones')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Remisiones';
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
        return redirect()->route('remisiones');
    }

}
