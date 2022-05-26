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
use Luecano\NumeroALetras\NumeroALetras;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlantillasFacturasExport;
use App\Imports\FacturasImport;
use App\Exports\FacturasExport;
use App\Remision;
use App\RemisionDetalle;
use App\OrdenTrabajo;
use App\OrdenTrabajoDetalle;
use App\Factura;
use App\FacturaDetalle;
use App\FacturaDocumento;
use App\Cliente;
use App\Almacen;
use App\Agente;
use App\Producto;
use App\Servicio;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\MetodoPago;
use App\UsoCFDI;
use App\c_TipoRelacion;
use App\c_RegimenFiscal;
use App\BitacoraDocumento;
Use App\Existencia;
use App\ClaveProdServ;
use App\ClaveUnidad;
use App\Comprobante;
use App\Configuracion_Tabla;
use App\VistaFactura;
use App\VistaObtenerExistenciaProducto;
use App\FolioComprobanteFactura;
use App\TipoOrdenCompra;
use App\TipoUnidad;
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\User_Rel_Serie;
use App\c_Periodicidad;
use App\c_Meses;
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use ZipArchive;
use File;

class FacturaController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //

    }

    public function facturas(){
        $contarseriesasignadasausuario = User_Rel_Serie::where('user_id', Auth::user()->id)->where('documento_serie', 'FACTURAS')->count();
        if($contarseriesasignadasausuario > 0){
            $contarserieusuario = DB::table('user_rel_series as urs')
            ->join('Folios Comprobantes Facturas as fcf', 'urs.serie_id', '=', 'fcf.Numero')
            ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Depto', 'fcf.Predeterminar')
            ->where('fcf.Predeterminar', '+')
            ->where('urs.user_id', Auth::user()->id)
            ->where('urs.documento_serie', 'FACTURAS')
            ->count();
            if($contarserieusuario == 0){
                $FolioComprobanteFactura = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Facturas as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Depto', 'fcf.Predeterminar')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'FACTURAS')
                ->orderBy('fcf.Numero', 'DESC')
                ->take(1)->get();
                $serieusuario = $FolioComprobanteFactura[0]->Serie;
                $esquema = $FolioComprobanteFactura[0]->Esquema;
                $depto = $FolioComprobanteFactura[0]->Depto;
            }else{
                $FolioComprobanteFactura = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Facturas as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Depto', 'fcf.Predeterminar')
                ->where('fcf.Predeterminar', '+')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'FACTURAS')
                ->first();
                $serieusuario = $FolioComprobanteFactura->Serie;
                $esquema = $FolioComprobanteFactura->Esquema;
                $depto = $FolioComprobanteFactura->Depto;
            }
        }else{
            $contarserieusuario = FolioComprobanteFactura::where('Predeterminar', '+')->count();
            if($contarserieusuario == 0){
                $FolioComprobanteFactura = FolioComprobanteFactura::orderBy('Numero','DESC')->take(1)->get();
                $serieusuario = $FolioComprobanteFactura[0]->Serie;
                $esquema = $FolioComprobanteFactura[0]->Esquema;
                $depto = $FolioComprobanteFactura[0]->Depto;
            }else{
                $FolioComprobanteFactura = FolioComprobanteFactura::where('Predeterminar', '+')->first();
                $serieusuario = $FolioComprobanteFactura->Serie;
                $esquema = $FolioComprobanteFactura->Esquema;
                $depto = $FolioComprobanteFactura->Depto;
            }
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Facturas', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('facturas_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('facturas_exportar_excel');
        $rutacreardocumento = route('facturas_generar_pdfs');
        $lugarexpedicion = $this->lugarexpedicion;
        $claveregimenfiscal = '';
        $regimenfiscal = '';
        if($this->regimenfiscal != ''){
            $c_RegimenFiscal = c_RegimenFiscal::where('Clave', $this->regimenfiscal)->first();
            $claveregimenfiscal = $c_RegimenFiscal->Clave;
            $regimenfiscal = $c_RegimenFiscal->Nombre;
        }
        $urlgenerarplantilla = route('facturas_generar_plantilla');
        return view('registros.facturas.facturas', compact('serieusuario','esquema','depto','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal','urlgenerarplantilla'));
    }

    public function facturas_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Facturas', Auth::user()->id);
            $fechahoy = Carbon::now()->toDateString();
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaFactura::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);//la consulta es dos veces mas rapido
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
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Factura .'\')">Cambios</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Factura .'\')">Bajas</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerkardex(\''.$data->Factura .'\')">Ver Movimientos</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('facturas_generar_pdfs_indiv',$data->Factura).'" target="_blank">Ver Documento Interno PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Factura .'\',1)">Enviar Documento Interno por Correo</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="'.route('facturas_generar_pdfs_cliente_indiv',$data->Factura).'" target="_blank">Ver Documento Cliente PDF</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Factura .'\',0)">Enviar Documento Cliente por Correo</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="timbrarfactura(\''.$data->Factura .'\')">Timbrar Factura</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="cancelartimbre(\''.$data->Factura .'\')">Cancelar Timbre</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="modificardatosgeneralesdocumento(\''.$data->Factura .'\')">Modificar Datos Generales</a></li>'.
                                                '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Factura .'\')">Imprimir Documento PDF</a></li>'.
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
                    ->addColumn('ImpLocTraslados', function($data){ return $data->ImpLocTraslados; })
                    ->addColumn('ImpLocRetenciones', function($data){ return $data->ImpLocRetenciones; })
                    ->addColumn('IepsRetencion', function($data){ return $data->IepsRetencion; })
                    ->addColumn('IsrRetencion', function($data){ return $data->IsrRetencion; })
                    ->addColumn('IvaRetencion', function($data){ return $data->IvaRetencion; })
                    ->addColumn('Ieps', function($data){ return $data->Ieps; })
                    //->addColumn('Descuento', function($data){ return $data->Descuento; })
                    //->addColumn('Importe', function($data){ return $data->Importe; })
                    ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                    //->addColumn('Costo', function($data){ return $data->Costo; })
                    //->addColumn('Comision', function($data){ return $data->Comision; })
                    //->addColumn('Utilidad', function($data){ return $data->Utilidad; })
                    ->rawColumns(['operaciones'])
                    ->make();
        }
    }
    //descargar plantilla
    public function facturas_generar_plantilla(){
        return Excel::download(new PlantillasFacturasExport(), "plantillafacturas.xlsx");
    }
    //cargar partidas excel
    public function facturas_cargar_partidas_excel(Request $request){
        $arrayexcel =  Excel::toArray(new FacturasImport, request()->file('partidasexcel'));
        $partidasexcel = $arrayexcel[0];
        $rowexcel = 0;
        $filasdetallesfactura = '';
        $contadorproductos = $request->contadorproductos;
        $contadorfilas = $request->contadorfilas;
        $numeropartida = $request->partida;
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
                        if(Helpers::convertirvalorcorrecto($cantidadpartida) == 0){
                            $cantidad = 1;
                        }else{
                            $cantidad = $cantidadpartida;
                        }
                        //precio de la partida
                        $preciopartida = $producto->SubTotal;
                        //importe de la partida
                        $importepartida =  $cantidad*$preciopartida;
                        //importe descuento
                        $importedescuentopesospartida =  $importepartida-0;
                        //subtotal de la partida
                        $subtotalpartida =  $importepartida-0;
                        //iva en pesos de la partida
                        $multiplicacionivapesospartida = $subtotalpartida*$producto->Impuesto;
                        $trasladoivapesospartida = $multiplicacionivapesospartida/100;
                        //total en pesos de la partida
                        $totalpesospartida = $subtotalpartida+$trasladoivapesospartida;
                        //costo total
                        $costototalpartida  = $producto->Costo*$cantidad;
                        //comision de la partida
                        $comisionporcentajepartida = $subtotalpartida*0;
                        $comisionespesospartida = $comisionporcentajepartida/100;
                        //utilidad de la partida
                        $utilidadpartida = $subtotalpartida-$costototalpartida-$comisionespesospartida;
                        $tipo = "alta";
                        $filasdetallesfactura= $filasdetallesfactura.
                        '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                            '<td class="tdmod"><div class="numeropartida">'.$numeropartida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                            '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$producto->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$producto->Codigo.'</b></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($producto->Producto, ENT_QUOTES).'"  data-parsley-length="[1, 255]" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$producto->Unidad.'" data-parsley-length="[1, 5]" autocomplete="off"></td>'.
                            '<td class="tdmod">'.
                                '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');">'.
                                '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($cantidad).'"" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                                '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                                '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                                '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($preciopartida).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($importepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentoporcentajepartida('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($importedescuentopesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($subtotalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');"></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($trasladoivapesospartida).'"  data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($totalpesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($producto->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($costototalpartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($comisionporcentajepartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculardescuentopesospartida('.$contadorfilas.');" required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($comisionespesospartida).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly required></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($utilidadpartida).'" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" autocomplete="off"></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($producto->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                        '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                        '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$producto->ClaveProducto.'" readonly required data-parsley-length="[1, 20]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveproductopartida" name="nombreclaveproductopartida[]"  value="'.$producto->NombreClaveProducto.'" readonly></td>'.
                            '<td class="tdmod">'.
                                '<div class="row divorinputmodxl">'.
                                    '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesunidades" data-toggle="tooltip" title="Ver Claves Unidades" onclick="listarclavesunidades('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                    '</div>'.
                                    '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                    '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$producto->ClaveUnidad.'" readonly required data-parsley-length="[1, 5]">'.
                                    '</div>'.
                                '</div>'.
                            '</td>'.
                            '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$producto->NombreClaveUnidad.'" readonly></td>'.
                        '</tr>';
                        array_push($arraycodigosyaagregados, $producto->Codigo);
                        $contadorproductos++;
                        $contadorfilas++;
                        $numeropartida++;
                    }
                }
            }
            $rowexcel++;
        }
        $data = array(
            "filasdetallesfactura" => $filasdetallesfactura,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "numeropartida" => $numeropartida
        );
        return response()->json($data);
    }

    //obtener ultimo folio
    public function facturas_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Factura', $request->serie);
        return response()->json($folio);
    }

    //obtener tipos
    public function facturas_obtener_tipos(){
        $tipos = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos = "<option  disabled hidden>Selecciona...</option>";
        foreach($tipos as $tipo){
            $select_tipos = $select_tipos."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos);
    }

    //obtener tipos de unidades
    public function facturas_obtener_tipos_unidades(){
        $tipos_unidades= TipoUnidad::where('STATUS', 'ALTA')->get();
        $select_tipos_unidades = "<option  disabled hidden>Selecciona...</option>";
        foreach($tipos_unidades as $tipo){
            $select_tipos_unidades = $select_tipos_unidades."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_unidades);
    }

    //obtener clientes
    public function facturas_obtener_clientes(Request $request){
        if($request->ajax()){
            //$data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            $data = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->leftJoin('c_RegimenFiscal as rf', 'rf.Clave', '=', 'c.RegimenFiscal')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre AS NombreUsoCfdi', 'p.Clave AS ClavePais', 'p.Nombre AS NombrePais', 'rf.Clave as ClaveRegimenFiscal', 'rf.Nombre as RegimenFiscal')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\','.$data->Agente.','.Helpers::convertirvalorcorrecto($data->Credito).','.Helpers::convertirvalorcorrecto($data->Saldo).',\''.$data->ClaveRegimenFiscal.'\',\''.$data->RegimenFiscal.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente por numero
    public function facturas_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $plazo = '';
        $credito = '';
        $saldo = '';
        $claveformapago = '';
        $formapago = '';
        $clavemetodopago = '';
        $metodopago = '';
        $claveusocfdi = '';
        $usocfdi = '';
        $claveresidenciafiscal = '';
        $residenciafiscal = '';
        $numeroagente = '';
        $nombreagente = '';
        $rfcagente = '';
        $claveregimenfiscalreceptor = '';
        $regimenfiscalreceptor = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $datos = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->leftJoin('c_RegimenFiscal as rf', 'rf.Clave', '=', 'c.RegimenFiscal')
            ->select('c.Numero', 'c.Status', 'fp.Clave AS claveformapago', 'fp.Nombre AS formapago', 'mp.Clave AS clavemetodopago', 'mp.Nombre AS metodopago', 'uc.Clave AS claveusocfdi', 'uc.Nombre AS usocfdi', 'p.Clave AS claveresidenciafiscal', 'p.Nombre AS residenciafiscal', 'rf.Clave as ClaveRegimenFiscal', 'rf.Nombre as RegimenFiscal')
            ->where('c.Numero', $request->numerocliente)
            ->where('c.Status', 'ALTA')
            ->get();
            $claveformapago = $datos[0]->claveformapago;
            $formapago = $datos[0]->formapago;
            $clavemetodopago = $datos[0]->clavemetodopago;
            $metodopago = $datos[0]->metodopago;
            $claveusocfdi = $datos[0]->claveusocfdi;
            $usocfdi = $datos[0]->usocfdi;
            $claveresidenciafiscal = $datos[0]->claveresidenciafiscal;
            $residenciafiscal = $datos[0]->residenciafiscal;
            $claveregimenfiscalreceptor = $datos[0]->ClaveRegimenFiscal;
            $regimenfiscalreceptor = $datos[0]->RegimenFiscal;
            $agente = Agente::where('Numero', $cliente->Agente)->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $plazo = $cliente->Plazo;
            $credito = Helpers::convertirvalorcorrecto($cliente->Credito);
            $saldo = Helpers::convertirvalorcorrecto($cliente->Saldo);
            $numeroagente = $agente->Numero;
            $nombreagente = $agente->Nombre;
            $rfcagente = $agente->Rfc;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc,
            'plazo' => $plazo,
            'credito' => $credito,
            'saldo' => $saldo,
            'claveformapago' => $claveformapago,
            'formapago' => $formapago,
            'clavemetodopago' => $clavemetodopago,
            'metodopago' => $metodopago,
            'claveusocfdi' => $claveusocfdi,
            'usocfdi' => $usocfdi,
            'claveresidenciafiscal' => $claveresidenciafiscal,
            'residenciafiscal' => $residenciafiscal,
            'claveregimenfiscalreceptor' => $claveregimenfiscalreceptor,
            'regimenfiscalreceptor' => $regimenfiscalreceptor,
            'numeroagente' => $numeroagente,
            'nombreagente' => $nombreagente,
            'rfcagente' => $rfcagente
        );
        return response()->json($data);
    }

    //obtener datos agente
    public function facturas_obtener_datos_agente(Request $request){
        $Agente = Agente::where('Numero', $request->NumeroAgente)->first();
        return response()->json($Agente);
    }

    //obtener agentes
    public function facturas_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre.'\',\''.$data->Rfc.'\')">Seleccionar</div>';
                    return $boton;
                })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener agente por numero
    public function facturas_obtener_agente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $existeagente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->count();
        if($existeagente > 0){
            $agente = Agente::where('Numero', $request->numeroagente)->where('Status', 'ALTA')->first();
            $numero = $agente->Numero;
            $nombre = $agente->Nombre;
            $rfc = $agente->Rfc;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc
        );
        return response()->json($data);
    }

    //obtener codigo postal
    public function facturas_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlugarexpedicion(\''.$data->Clave .'\',\''.$data->Estado .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener lugar expedicion por clave
    public function facturas_obtener_lugar_expedicion_por_clave(Request $request){
        $clave = '';
        $estado = '';
        $existelugarexpedicion = CodigoPostal::where('Clave', $request->lugarexpedicion)->count();
        if($existelugarexpedicion > 0){
            $lugarexpedicion = CodigoPostal::where('Clave', $request->lugarexpedicion)->first();
            $clave = $lugarexpedicion->Clave;
            $estado = $lugarexpedicion->Estado;
        }
        $data = array(
            'clave' => $clave,
            'estado' => $estado
        );
        return response()->json($data);
    }

    //obtener regimen fiscal
    public function facturas_obtener_regimenes_fiscales(Request $request){
        if($request->ajax()){
            $data = c_RegimenFiscal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarregimenfiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener regimen fiscal por clave
    public function facturas_obtener_regimen_fiscal_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeregimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscal)->count();
        if($existeregimenfiscal > 0){
            $regimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscal)->first();
            $clave = $regimenfiscal->Clave;
            $nombre = $regimenfiscal->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener tipo relacion
    public function facturas_obtener_tipos_relacion(Request $request){
        if($request->ajax()){
            $data = c_TipoRelacion::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionartiporelacion(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener tipo relacion or clave
    public function facturas_obtener_tipo_relacion_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeretiporelacion = c_TipoRelacion::where('Clave', $request->clavetiporelacion)->count();
        if($existeretiporelacion > 0){
            $tiporelacion = c_TipoRelacion::where('Clave', $request->clavetiporelacion)->first();
            $clave = $tiporelacion->Clave;
            $nombre = $tiporelacion->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener formas pago
    public function facturas_obtener_formas_pago(Request $request){
        if($request->ajax()){
            $data = FormaPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarformapago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener forma pago por clave
    public function facturas_obtener_forma_pago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existereformapago = FormaPago::where('Clave', $request->claveformapago)->count();
        if($existereformapago > 0){
            $formapago = FormaPago::where('Clave', $request->claveformapago)->first();
            $clave = $formapago->Clave;
            $nombre = $formapago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener metodos pago
    public function facturas_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $data = MetodoPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener forma pago por clave
    public function facturas_obtener_metodo_pago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeremetodopago = MetodoPago::where('Clave', $request->clavemetodopago)->count();
        if($existeremetodopago > 0){
            $metodopago = MetodoPago::where('Clave', $request->clavemetodopago)->first();
            $clave = $metodopago->Clave;
            $nombre = $metodopago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener usos cfdi
    public function facturas_obtener_usos_cfdi(Request $request){
        if($request->ajax()){
            $data = UsoCFDI::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarusocfdi(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener forma pago por clave
    public function facturas_obtener_uso_cfdi_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existereusocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->count();
        if($existereusocfdi > 0){
            $usocfdi = UsoCFDI::where('Clave', $request->claveusocfdi)->first();
            $clave = $usocfdi->Clave;
            $nombre = $usocfdi->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener residencias fiscales
    public function facturas_obtener_residencias_fiscales(Request $request){
        if($request->ajax()){
            $data = Pais::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarresidenciafiscal(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener
    public function facturas_obtener_regimenes_fiscales_receptor(Request $request){
        if($request->ajax()){
            $data = c_RegimenFiscal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarregimenfiscalreceptor(\''.$data->Clave .'\',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener forma pago por clave
    public function facturas_obtener_regimenfiscalreceptor_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeregimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscalreceptor)->count();
        if($existeregimenfiscal > 0){
            $regimenfiscal = c_RegimenFiscal::where('Clave', $request->claveregimenfiscalreceptor)->first();
            $clave = $regimenfiscal->Clave;
            $nombre = $regimenfiscal->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }

    //obtener periodicidades
    public function facturas_obtener_periodicidades(Request $request){
        if($request->ajax()){
            $data = c_Periodicidad::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarperiodicidad(\''.$data->Clave .'\',\''.$data->Descripcion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener periodicidad por clave
    public function facturas_obtener_periodicidad_por_clave(Request $request){
        $clave = '';
        $descripcion = '';
        $existeperiodicidad = c_Periodicidad::where('Clave', $request->claveperiodicidad)->count();
        if($existeperiodicidad > 0){
            $periodicidad = c_Periodicidad::where('Clave', $request->claveperiodicidad)->first();
            $clave = $periodicidad->Clave;
            $descripcion = $periodicidad->Descripcion;
        }
        $data = array(
            'clave' => $clave,
            'descripcion' => $descripcion
        );
        return response()->json($data);

    }

    //obtener meses
    public function facturas_obtener_meses(Request $request){
        if($request->ajax()){
            $data = c_Meses::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmes(\''.$data->Clave .'\',\''.$data->Descripcion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener mes por clave
    public function facturas_obtener_mes_por_clave(Request $request){
        $clave = '';
        $descripcion = '';
        $existemes = c_Meses::where('Clave', $request->clavemes)->count();
        if($existemes > 0){
            $mes = c_Meses::where('Clave', $request->clavemes)->first();
            $clave = $mes->Clave;
            $descripcion = $mes->Descripcion;
        }
        $data = array(
            'clave' => $clave,
            'descripcion' => $descripcion
        );
        return response()->json($data);

    }

    //obtener forma pago por clave
    public function facturas_obtener_residencia_fiscal_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeresidenciafiscal = Pais::where('Clave', $request->claveresidenciafiscal)->count();
        if($existeresidenciafiscal > 0){
            $residencialfiscal = Pais::where('Clave', $request->claveresidenciafiscal)->first();
            $clave = $residencialfiscal->Clave;
            $nombre = $residencialfiscal->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre
        );
        return response()->json($data);
    }


    //obtener folios fiscales
    public function facturas_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $contarseriesasignadasausuario = User_Rel_Serie::where('user_id', Auth::user()->id)->where('documento_serie', 'FACTURAS')->count();
            if($contarseriesasignadasausuario > 0){
                $data = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Facturas as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Depto', 'fcf.Status')
                ->where('fcf.Status', 'ALTA')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'FACTURAS')
                ->orderby('fcf.Numero', 'DESC')
                ->get();
            }else{
                $data = FolioComprobanteFactura::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
            }
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfoliofiscal(\''.$data->Serie.'\',\''.$data->Esquema.'\',\''.$data->Depto.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener ultimo folio de la serie seleccionada
    public function facturas_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\Factura', $request->Serie);
        return response()->json($folio);
    }

    //obtener serie interna
    public function facturas_obtener_serie_interna(){
        $foliointernofactura = FolioComprobanteFactura::where('Esquema', 'INTERNA')->orderby('Numero', 'ASC')->take(1)->get();
        $serieesquemainterno = Factura::select("Folio")->where('Serie', ''.$foliointernofactura[0]->Serie.'')->orderBy("Folio", "DESC")->take(1)->get();
        if(sizeof($serieesquemainterno) == 0 || sizeof($serieesquemainterno) == "" || sizeof($serieesquemainterno) == null){
            $folio = 1;
        }else{
            $folio = $serieesquemainterno[0]->Folio+1;
        }
        $data = array(
            'Folio' => $folio,
            'Serie' => $foliointernofactura[0]->Serie,
            'Esquema' => $foliointernofactura[0]->Esquema,
            'Depto' => $foliointernofactura[0]->Depto
        );
        return response()->json($data);
    }

    //obtener remisiones del cliente
    public function facturas_obtener_remisiones(Request $request){
        if($request->ajax()){
            $arrayremisionesseleccionadas = Array();
            foreach(explode(",", $request->stringremisionesseleccionadas) as $remision){
                array_push($arrayremisionesseleccionadas, $remision);
            }
            $data = Remision:://where('Cliente', $request->numerocliente)
                                //->whereNotIn('Remision', $arrayremisionesseleccionadas)
                                where('Status', 'POR FACTURAR')
                                ->where('Total', '>', 0)
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('NombreCliente', function($data){
                        $cliente = Cliente::where('Numero', $data->Cliente)->first();
                        return $cliente->Nombre;
                    })
                    ->addColumn('Facturar', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Selecciona', function($data) use ($arrayremisionesseleccionadas){
                        if(in_array($data->Remision, $arrayremisionesseleccionadas) == true){
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required checked>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }else{
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }
                        return $checkbox;
                    })
                    ->rawColumns(['operaciones','Fecha','NombreCliente','Facturar','Selecciona'])
                    ->make(true);
        }
    }

    public function facturas_obtener_remisiones_por_pedido(Request $request){
        if($request->ajax()){
            $numeropedidoremisiones = $request->numeropedidoremisiones;
            $data = Remision:://where('Cliente', $request->numerocliente)
                                where('Status', 'POR FACTURAR')
                                ->where('Total', '>', 0)
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('NombreCliente', function($data){
                        $cliente = Cliente::where('Numero', $data->Cliente)->first();
                        return $cliente->Nombre;
                    })
                    ->addColumn('Facturar', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Selecciona', function($data) use ($numeropedidoremisiones){
                        if($data->Pedido == $numeropedidoremisiones){
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required checked>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }else{
                            $checkbox = '<input type="checkbox" name="remisionesseleccionadas[]" id="idremisionesseleccionadas'.$data->Remision.'" class="remisionesseleccionadas filled-in" value="'.$data->Remision.'" onchange="seleccionarremision('.$data->Folio.',\''.$data->Remision.'\','.$data->Cliente.');" required>'.
                            '<label for="idremisionesseleccionadas'.$data->Remision.'" ></label>';
                        }
                        return $checkbox;
                    })
                    ->rawColumns(['operaciones','Fecha','NombreCliente','Facturar','Selecciona'])
                    ->make(true);
        }

    }

    public function facturas_obtener_remisiones_pedido(Request $request){
        $remisiones = Remision::where('Cliente', $request->numerocliente)
                            ->where('Pedido', $request->numeropedidoremisiones)
                            ->where('Status', 'POR FACTURAR')
                            ->where('Total', '>', 0)
                            ->orderBy('Folio', 'DESC')
                            ->get();
            //detalles remision
            $filasremisiones = '';
            $contadorfilas = $request->contadorfilas;
            $partida = $request->partida;
        //dd($remisiones);
        foreach($remisiones as $r){
            $remision = Remision::where('Remision', $r->Remision)->first();
            $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($remision->Iva, $remision->SubTotal);
            $tipooperacion = $request->tipooperacion;
            $detallesremision = RemisionDetalle::where('Remision', $r->Remision)->OrderBy('Item', 'ASC')->get();
            foreach($detallesremision as $detalle){
                $ImporteDescuento = $detalle->Importe - $detalle->Descuento;
                $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                $claveproductopartida = ClaveProdServ::where('Clave', $producto->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $producto->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasremisiones= $filasremisiones.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]">'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]">'.$detalle->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="'.$detalle->Remision.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$detalle->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="'.$remision->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$detalle->Moneda.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $partida++;
            }
        }
        $data = array(
            "filasremisiones" => $filasremisiones,
            "contadorfilas" => $contadorfilas,
            "partida" => $partida,
            "saldo" => Helpers::convertirvalorcorrecto($remision->Saldo)
        );
        return response()->json($data);
    }

    //obtener factura
    public function facturas_obtener_total_a_facturar(Request $request){
        $totalafacturar = 0;
        if($request->tipo == "REMISIONES"){
            foreach(explode(",", $request->stringremisionesseleccionadas) as $r){
                $detallesremision = RemisionDetalle::where('Remision', $r)->OrderBy('Item', 'ASC')->get();
                foreach($detallesremision as $detalle){
                    $totalafacturar = $totalafacturar + $detalle->Total;
                }
            }
        }else{
            foreach(explode(",", $request->stringordenesseleccionadas) as $o){
                $detallesorden = OrdenTrabajoDetalle::where('Orden', $o)->OrderBy('Item', 'ASC')->get();
                foreach($detallesorden as $detalle){
                    $totalafacturar = $totalafacturar + $detalle->Total;
                }
            }
        }

        return response()->json(Helpers::convertirvalorcorrecto($totalafacturar));
    }

    //obtener remision
    public function facturas_obtener_remision(Request $request){
        //detalles remision
        $filasremisiones = '';
        $contadorfilas = $request->contadorfilas;
        $contadorproductos = $request->contadorproductos;
        $partida = $request->partida;
        foreach(explode(",", $request->stringremisionesseleccionadas) as $r){
            $remision = Remision::where('Remision', $r)->first();
            $cliente = $remision->Cliente;
            $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($remision->Iva, $remision->SubTotal);
            $tipooperacion = $request->tipooperacion;
            $detallesremision = RemisionDetalle::where('Remision', $r)->OrderBy('Item', 'ASC')->get();
            foreach($detallesremision as $detalle){
                $ImporteDescuento = $detalle->Importe - $detalle->Descuento;
                $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                $claveproductopartida = ClaveProdServ::where('Clave', $producto->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $producto->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasremisiones= $filasremisiones.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]">'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]">'.$detalle->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="'.$detalle->Remision.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$detalle->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="'.$remision->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$detalle->Moneda.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $partida++;
                $contadorproductos++;
            }
            /*
            $remision = Remision::where('Remision', $request->Remision)->first();
            $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($remision->Iva, $remision->SubTotal);
            $tipooperacion = $request->tipooperacion;
            //detalles remision
            $filasremisiones = '';
            $contadorfilas = $request->contadorfilas;
            $partida = $request->partida;;
            $detallesremision = RemisionDetalle::where('Remision', $request->Remision)->OrderBy('Item', 'ASC')->get();
            foreach($detallesremision as $detalle){
                $ImporteDescuento = $detalle->Importe - $detalle->Descuento;
                $producto = Producto::where('Codigo', $detalle->Codigo)->first();
                $claveproductopartida = ClaveProdServ::where('Clave', $producto->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $producto->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasremisiones= $filasremisiones.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]">'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]">'.$detalle->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="'.$detalle->Remision.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="REFACCIONES" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$detalle->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="'.$remision->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$detalle->Moneda.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $partida++;
            }
            */
        }
        $data = array(
            "filasremisiones" => $filasremisiones,
            "contadorfilas" => $contadorfilas,
            "contadorproductos" => $contadorproductos,
            "partida" => $partida,
            "saldo" => Helpers::convertirvalorcorrecto($remision->Saldo),
            "cliente" => $cliente
        );
        return response()->json($data);
    }

    //obtener servicios
    public function facturas_obtener_ordenes(Request $request){
        if($request->ajax()){
            $arrayordenesseleccionadas= Array();
            foreach(explode(",", $request->stringordenesseleccionadas) as $orden){
                array_push($arrayordenesseleccionadas, $orden);
            }
            $data = OrdenTrabajo:://where('Cliente', $request->numerocliente)
                                where('Status', 'CERRADA')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('NombreCliente', function($data){
                        $cliente = Cliente::where('Numero', $data->Cliente)->first();
                        return $cliente->Nombre;
                    })
                    ->addColumn('Facturar', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total-$data->Facturado);
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Selecciona', function($data) use ($arrayordenesseleccionadas){
                        if(in_array($data->Orden, $arrayordenesseleccionadas) == true){
                            $checkbox = '<input type="checkbox" name="ordenesseleccionadas[]" id="idordenesseleccionadas'.$data->Orden.'" class="ordenesseleccionadas filled-in" value="'.$data->Orden.'" onchange="seleccionarorden(\''.$data->Orden.'\','.$data->Cliente.');" required checked>'.
                            '<label for="idordenesseleccionadas'.$data->Orden.'" ></label>';
                        }else{
                            $checkbox = '<input type="checkbox" name="ordenesseleccionadas[]" id="idordenesseleccionadas'.$data->Orden.'" class="ordenesseleccionadas filled-in" value="'.$data->Orden.'" onchange="seleccionarorden(\''.$data->Orden.'\','.$data->Cliente.');" required>'.
                            '<label for="idordenesseleccionadas'.$data->Orden.'" ></label>';
                        }
                        return $checkbox;
                    })
                    ->rawColumns(['operaciones','Fecha','NombreCliente','Facturar','Total','Selecciona'])
                    ->make(true);
        }
    }

    //obtener detalles orden
    public function facturas_obtener_orden(Request $request){
        $filasordenes = '';
        $contadorfilas = $request->contadorfilas;
        $contadorproductos = $request->contadorproductos;
        $partida = $request->partida;
        foreach(explode(",", $request->stringordenesseleccionadas) as $o){
            $orden = OrdenTrabajo::where('Orden', $o)->first();
            $cliente = $orden->Cliente;
            $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($orden->Iva, $orden->SubTotal);
            $tipooperacion = $request->tipooperacion;
            //detalles orden
            $detallesorden = OrdenTrabajoDetalle::where('Orden', $o)->OrderBy('Item', 'ASC')->get();
            foreach($detallesorden as $detalle){
                $ImporteDescuento = $detalle->Importe - $detalle->Descuento;
                if($detalle->Departamento == 'REFACCIONES' || $detalle->Departamento == 'Compra'){
                    $departamento = Producto::where('Codigo', $detalle->Codigo)->first();
                }else if($detalle->Departamento == 'SERVICIO'){
                    $departamento = Servicio::where('Codigo', $detalle->Codigo)->first();
                    if($departamento == null){
                        $departamento = Servicio::where('Codigo','LIKE', '%'.$detalle->Codigo.'%')->first();
                    }
                }
                $claveproductopartida = ClaveProdServ::where('Clave', $departamento->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $departamento->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasordenes= $filasordenes.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]">'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]">'.$detalle->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="'.$detalle->Orden.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="'.$detalle->Departamento.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="'.$detalle->Cargo.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$detalle->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="0" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $partida++;
                $contadorproductos++;
            }
            /*
            $orden = OrdenTrabajo::where('Orden', $request->Orden)->first();
            $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($orden->Iva, $orden->SubTotal);
            $tipooperacion = $request->tipooperacion;
            //detalles orden
            $filasordenes = '';
            $contadorfilas = $request->contadorfilas;
            $partida = $request->partida;;
            $detallesorden = OrdenTrabajoDetalle::where('Orden', $request->Orden)->OrderBy('Item', 'ASC')->get();
            foreach($detallesorden as $detalle){
                $ImporteDescuento = $detalle->Importe - $detalle->Descuento;
                if($detalle->Departamento == 'REFACCIONES' || $detalle->Departamento == 'Compra'){
                    $departamento = Producto::where('Codigo', $detalle->Codigo)->first();
                }else if($detalle->Departamento == 'SERVICIO'){
                    $departamento = Servicio::where('Codigo', $detalle->Codigo)->first();
                }
                $claveproductopartida = ClaveProdServ::where('Clave', $departamento->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $departamento->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasordenes= $filasordenes.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$detalle->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$detalle->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'" required data-parsley-length="[1, 255]">'.htmlspecialchars($detalle->Descripcion, ENT_QUOTES).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$detalle->Unidad.'" required data-parsley-length="[1, 5]">'.$detalle->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($detalle->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($detalle->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="'.$detalle->Orden.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="SERVICIO" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="SERVICIO" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$detalle->Item.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm tiendapartida" name="tiendapartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm pedidopartida" name="pedidopartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="0" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="MXN" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                $contadorfilas++;
                $partida++;
            }
            */
        }
        $data = array(
            "filasordenes" => $filasordenes,
            "contadorfilas" => $contadorfilas,
            "contadorproductos" => $contadorproductos,
            "partida" => $partida,
            "cliente" => $cliente,
            "pedido" => $orden->Pedido
        );
        return response()->json($data);
    }
    //obtener productos
    public function facturas_obtener_productos(Request $request){
        if($request->ajax()){
            $codigoabuscar = $request->codigoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigoabuscar . '%');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    public function facturas_obtener_producto_por_codigo(Request $request){
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
    //obtener productos gastos
    public function facturas_obtener_productos_gastos(Request $request){
        if($request->ajax()){
            $codigogastoabuscar = $request->codigogastoabuscar;
            $tipooperacion = $request->tipooperacion;
            $data = VistaObtenerExistenciaProducto::where('Codigo', 'like', '%' . $codigogastoabuscar . '%')->where('TipoProd', 'GASTOS');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Producto, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Costo).'\',\''.Helpers::convertirvalorcorrecto($data->Impuesto).'\',\''.Helpers::convertirvalorcorrecto($data->SubTotal).'\',\''.$tipooperacion.'\',\''.$data->Insumo.'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->CostoDeLista).'\')">Seleccionar</div>';
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
    //producto gasto por codigo
    public function facturas_obtener_producto_gasto_por_codigo(Request $request){
        $codigogastoabuscar = $request->codigogastoabuscar;
        $contarproductos = VistaObtenerExistenciaProducto::where('Codigo', $codigogastoabuscar)->where('TipoProd', 'GASTOS')->count();
        if($contarproductos > 0){
            $producto = VistaObtenerExistenciaProducto::where('Codigo', $codigogastoabuscar)->where('TipoProd', 'GASTOS')->first();
            $data = array(
                'Codigo' => $producto->Codigo,
                'Producto' => htmlspecialchars($producto->Producto, ENT_QUOTES),
                'Unidad' => $producto->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($producto->Costo),
                'Impuesto' => Helpers::convertirvalorcorrecto($producto->Impuesto),
                'SubTotal' => Helpers::convertirvalorcorrecto($producto->SubTotal),
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

    public function facturas_obtener_servicios(Request $request){
        if($request->ajax()){
            $codigoservicioabuscar = $request->codigoservicioabuscar;
            $tipooperacion = $request->tipooperacion;
            //$data = Servicio::where('Codigo', 'like', '%'.$codigoservicioabuscar.'%')->where('Status', 'ALTA');
            $data = DB::table('Servicios as s')
            ->leftjoin('c_ClaveProdServ as cps', 's.ClaveProducto', '=', 'cps.Clave')
            ->leftjoin('c_ClaveUnidad as cu', 's.ClaveUnidad', '=', 'cu.Clave')
            ->select('s.Codigo', 's.Servicio', 's.Unidad', 's.Costo', 's.Venta', "s.Cantidad", 's.ClaveProducto', 's.ClaveUnidad', 'cps.Nombre as NombreClaveProducto', 'cu.Nombre as NombreClaveUnidad')
            ->where('Codigo', 'like', '%'.$codigoservicioabuscar.'%')
            ->where('Status', 'ALTA');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($tipooperacion){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="agregarfilaproducto(\''.$data->Codigo .'\',\''.htmlspecialchars($data->Servicio, ENT_QUOTES).'\',\''.$data->Unidad .'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.Helpers::convertirvalorcorrecto(16).'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.$tipooperacion.'\',\'\',\''.$data->ClaveProducto.'\',\''.$data->ClaveUnidad.'\',\''.$data->NombreClaveProducto.'\',\''.$data->NombreClaveUnidad.'\',\''.Helpers::convertirvalorcorrecto($data->Venta).'\',\''.Helpers::convertirvalorcorrecto($data->Cantidad).'\')">Seleccionar</div>';
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

    }
    public function facturas_obtener_servicio_por_codigo(Request $request){
        $codigoservicioabuscar = $request->codigoservicioabuscar;
        $contarservicios = Servicio::where('Codigo', $codigoservicioabuscar)->where('Status', 'ALTA')->count();
        if($contarservicios > 0){
            $servicio = Servicio::where('Codigo', $codigoservicioabuscar)->where('Status', 'ALTA')->first();
            $data = array(
                'Codigo' => $servicio->Codigo,
                'Servicio' => htmlspecialchars($servicio->Servicio, ENT_QUOTES),
                'Unidad' => $servicio->Unidad,
                'Costo' => Helpers::convertirvalorcorrecto($servicio->Costo),
                'Venta' => Helpers::convertirvalorcorrecto($servicio->Venta),
                'Cantidad' => Helpers::convertirvalorcorrecto($servicio->Cantidad),
                'ClaveProducto' => $servicio->ClaveProducto,
                'ClaveUnidad' => $servicio->ClaveUnidad,
                'contarservicios' => $contarservicios
            );
        }else{
            $data = array(
                'Codigo' => '',
                'Servicio' => '',
                'Unidad' => '',
                'Costo' => '',
                'Venta' => '',
                'Cantidad' => '',
                'ClaveProducto' => '',
                'ClaveUnidad' => '',
                'contarservicios' => $contarservicios
            );
        }
        return response()->json($data);
    }

    //obtener claves productos
    public function facturas_obtener_claves_productos(Request $request){
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
    public function facturas_obtener_claves_unidades(Request $request){
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
    //obtener facturas relaciinadas
    public function facturas_obtener_facturas_relacionadas(Request $request){
        if($request->ajax()){
            $data = Factura::where('Status', 'BAJA')->where('Cliente', $request->numerocliente)->where('UUID', '<>', '');
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfacturarel(\''.$data->UUID .'\',\''.$data->Factura.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }

    }

    //cargar uuid relacionado
    public function facturas_cargar_xml_uuid_relacionado(Request $request){
        $tipooperacion = $request->tipooperacion;
        $mover_a_carpeta="xml_cargados";
        $xml = $request->xml;
        $nombre_original = $xml->getClientOriginalName();
        //guardar xml en public/xml_cargados
        $xml->move($mover_a_carpeta,$nombre_original);
        if (file_exists('xml_cargados/'.$nombre_original)) {
            //cargar xml
            $xml = simplexml_load_file('xml_cargados/'.$nombre_original);
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $uuid = $atributos_complemento['UUID'];
            $uuidrelacionado =  '<tr class="filasuuid" id="filauuid0">'.
                                    '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminaruuid" onclick="eliminarfilauuid(0)">X</div><input type="hidden" class="form-control uuidagregadoen" name="uuidagregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm uuidrelacionado" name="uuidrelacionado[]" value="'.$uuid.'" readonly>'.$uuid.'</td>'.
                                '</tr>';
            //eliminar xml de public/xml_cargados
            $eliminarxml = public_path().'/xml_cargados/'.$nombre_original;
            unlink($eliminarxml);
        } else {
            exit('Error al abrir xml.');
        }
        $data = array(
            'uuid' => $uuid,
            'uuidrelacionado' => $uuidrelacionado
        );
        return response()->json($data);
    }
    //obtener nuevo saldo cliente
    public function facturas_obtener_nuevo_saldo_cliente(Request $request){
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        ///$nuevosaldo = $cliente->Saldo + $request->total;
        return response()->json(Helpers::convertirvalorcorrecto($cliente->Saldo));
    }

    //alta
    public function facturas_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\Factura', $request->serie);
        //INGRESAR DATOS A TABLA
        $factura = $folio.'-'.$request->serie;
        $Factura = new Factura;
        $Factura->Factura=$factura;
        $Factura->Serie=$request->serie;
        $Factura->Folio=$folio;
        $Factura->Esquema=$request->esquema;
        $Factura->Cliente=$request->numerocliente;
        $Factura->Agente=$request->numeroagente;
        $Factura->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $Factura->Plazo=$request->plazo;
        $Factura->Depto=$request->depto;
        $Factura->Pedido=$request->pedido;
        $Factura->Tipo=$request->tipo;
        $Factura->Unidad=$request->tipounidad;
        $Factura->Importe=$request->importe;
        $Factura->Descuento=$request->descuento;
        $Factura->SubTotal=$request->subtotal;
        $Factura->Iva=$request->iva;
        $Factura->Total=$request->total;
        $Factura->Costo=$request->costo;
        $Factura->Comision=$request->comision;
        $Factura->Utilidad=$request->utilidad;
        $Factura->Saldo=$request->total;
        $Factura->Moneda=$request->moneda;
        $Factura->TipoCambio=$request->pesosmoneda;
        $Factura->Obs=$request->observaciones;
        $Factura->Descripcion=$request->descripcion;
        $Factura->Status="POR COBRAR";
        $Factura->Usuario=Auth::user()->user;
        $Factura->CondicionesDePago=$request->condicionesdepago;
        $Factura->LugarExpedicion=$request->lugarexpedicion;
        $Factura->RegimenFiscal=$request->claveregimenfiscal;
        $Factura->TipoRelacion=$request->clavetiporelacion;
        $Factura->Confirmacion=$request->confirmacion;
        $Factura->FormaPago=$request->claveformapago;
        $Factura->MetodoPago=$request->clavemetodopago;
        $Factura->UsoCfdi=$request->claveusocfdi;
        $Factura->ResidenciaFiscal=$request->claveresidenciafiscal;
        $Factura->NumRegIdTrib=$request->numeroregidtrib;
        $Factura->EmisorRfc=$request->emisorrfc;
        $Factura->EmisorNombre=$request->emisornombre;
        $Factura->ReceptorRfc=$request->receptorrfc;
        $Factura->ReceptorNombre=$request->receptornombre;
        $Factura->RegimenFiscalReceptor=$request->claveregimenfiscalreceptor;
        $Factura->Hora=Carbon::parse($request->fecha)->toDateTimeString();
        $Factura->Periodo=$this->periodohoy;
        $Factura->Periodicidad=$request->claveperiodicidad;
        $Factura->Meses=$request->clavemes;
        $Factura->save();
        //Modificar saldo cliente
        $cliente = Cliente::where('Numero', $request->numerocliente)->first();
        $nuevosaldo = $cliente->Saldo + $request->total;
        Cliente::where('Numero', $request->numerocliente)
                            ->update([
                                'Saldo' => Helpers::convertirvalorcorrecto($nuevosaldo)
                            ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "FACTURAS";
        $BitacoraDocumento->Movimiento = $factura;
        $BitacoraDocumento->Aplicacion = "POR COBRAR";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "POR COBRAR";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA  DETALLES
        $item = 1;
        foreach ($request->codigopartida as $key => $codigopartida){
            $FacturaDetalle=new FacturaDetalle;
            $FacturaDetalle->Factura = $factura;
            $FacturaDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
            $FacturaDetalle->Codigo = $codigopartida;
            $FacturaDetalle->Descripcion = $request->descripcionpartida [$key];
            $FacturaDetalle->Unidad = $request->unidadpartida [$key];
            $FacturaDetalle->Cantidad =  $request->cantidadpartida  [$key];
            $FacturaDetalle->Precio =  $request->preciopartida [$key];
            $FacturaDetalle->Importe = $request->importepartida [$key];
            $FacturaDetalle->Dcto = $request->descuentoporcentajepartida [$key];
            $FacturaDetalle->Descuento = $request->descuentopesospartida [$key];
            $FacturaDetalle->ImporteDescuento = $request->importedescuentopesospartida [$key];
            $FacturaDetalle->SubTotal = $request->subtotalpartida [$key];
            $FacturaDetalle->Impuesto = $request->ivaporcentajepartida [$key];
            $FacturaDetalle->Iva = $request->trasladoivapesospartida [$key];
            $FacturaDetalle->Total = $request->totalpesospartida [$key];
            $FacturaDetalle->Costo = $request->costopartida [$key];
            $FacturaDetalle->CostoTotal = $request->costototalpartida [$key];
            $FacturaDetalle->Com = $request->comisionporcentajepartida [$key];
            $FacturaDetalle->Comision = $request->comisionpesospartida [$key];
            $FacturaDetalle->Utilidad = $request->utilidadpartida [$key];
            $FacturaDetalle->Moneda = $request->monedapartida [$key];
            $FacturaDetalle->CostoDeLista = $request->costodelistapartida [$key];
            $FacturaDetalle->TipoDeCambio = $request->tipocambiopartida [$key];
            $FacturaDetalle->Remision = $request->remisionpartida [$key];
            $FacturaDetalle->Orden = $request->ordenpartida [$key];
            $FacturaDetalle->Departamento = $request->departamentopartida [$key];
            $FacturaDetalle->Cargo = $request->cargopartida [$key];
            $FacturaDetalle->Partida = $request->partida [$key];
            $FacturaDetalle->Facturar = $request->depto;
            $FacturaDetalle->Tienda = $request->tiendapartida [$key];
            $FacturaDetalle->Pedido = $request->pedidopartida [$key];
            $FacturaDetalle->Almacen = $request->almacenpartida [$key];
            $FacturaDetalle->DatosUnidad = $request->datosunidadpartida [$key];
            $FacturaDetalle->ClaveProducto = $request->claveproductopartida [$key];
            $FacturaDetalle->ClaveUnidad = $request->claveunidadpartida [$key];
            $FacturaDetalle->Item = $item;
            $FacturaDetalle->save();
            $item++;
            switch ($request->depto) {
                case "SERVICIO":
                    OrdenTrabajo::where('Orden', $request->ordenpartida [$key])
                            ->update([
                                'Status' => $factura
                            ]);
                    OrdenTrabajoDetalle::where('Orden', $request->ordenpartida [$key])
                            ->where('Partida', $request->partida [$key])
                            ->update([
                                'Status' => $factura
                            ]);
                    break;
                case "PRODUCTOS":
                    //si el producto fue cargador por remision en la tpartida tendra el valor si no es un producto por gasto
                    if($request->remisionpartida [$key] != ''){
                        Remision::where('Remision', $request->remisionpartida [$key])
                                ->update([
                                    'Status' => $factura
                                ]);
                    }
                    break;
            }
        }
        //INGRESAR DATOS A TABLA  DOCUMENTOS
        if($request->numerofilasuuid > 0){
            foreach ($request->uuidrelacionado as $key => $uuidrelacionado){
                $FacturaDocumento=new FacturaDocumento;
                $FacturaDocumento->Factura = $factura;
                $FacturaDocumento->UUID = $uuidrelacionado;
                $FacturaDocumento->save();
            }
        }
        return response()->json($Factura);
    }

    //obtener registro
    public function facturas_obtener_factura(Request $request){
        $factura = Factura::where('Factura', $request->facturamodificar)->first();
        $cliente = Cliente::where('Numero', $factura->Cliente)->first();
        $agente = Agente::where('Numero', $factura->Agente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $factura->RegimenFiscal)->first();
        $formapago = FormaPago::where('Clave', $factura->FormaPago)->first();
        $metodopago = MetodoPago::where('Clave', $factura->MetodoPago)->first();
        $usocfdi = UsoCFDI::where('Clave', $factura->UsoCfdi)->first();
        $residenciafiscal = Pais::where('Clave', $factura->ResidenciaFiscal)->first();
        $regimenfiscalreceptor = c_RegimenFiscal::where('Clave', $factura->RegimenFiscalReceptor)->first();
        $periodicidad = c_Periodicidad::where('Clave', $factura->Periodicidad)->first();
        $meses = c_Meses::where('Clave', $factura->Meses)->first();
        $nombretiporelacion = "";
        $clavetiporelacion = "";
        $contartiporelacion = c_TipoRelacion::where('Clave', $factura->TipoRelacion)->count();
        if($contartiporelacion > 0){
            $tiporelacion = c_TipoRelacion::where('Clave', $factura->TipoRelacion)->first();
            $nombretiporelacion = $tiporelacion->Nombre;
            $clavetiporelacion = $tiporelacion->Clave;
        }
        //ver si se puede modificar la factura siempre y cuando sea una factura libre y no tenga ningun documento ligado a ella
        switch($factura->Serie){
            case "LST":
                $numerocuentasporcobrarligadas = CuentaXCobrarDetalle::where('Factura', $factura->Factura)->where('Abono', '>', 0)->count();
                $numeronotascreditoligadas = NotaClienteDocumento::where('Factura', $factura->Factura)->where('Descuento', '>', 0)->count();
                if($numerocuentasporcobrarligadas == 0 && $numeronotascreditoligadas == 0){
                    $readonly = "";
                }else{
                    $readonly = "readonly";
                }
                break;
            default:
                $readonly = "readonly";
        }
        //detalles
        $consarrayremisiones = array();
        $consarrayordenes = array();
        $arrayremisiones = array();
        $arrayordenes = array();
        $detallesfactura = FacturaDetalle::where('Factura', $request->facturamodificar)->orderBy('Item', 'ASC')->get();
        $numerodetallesfactura = FacturaDetalle::where('Factura', $request->facturamodificar)->count();
        $filasdetallesfactura= '';
        if($numerodetallesfactura > 0){
            $contadorproductos = 0;
            $contadorfilas = 0;
            $partida = 1;
            $tipo="modificacion";
            foreach($detallesfactura as $df){
                $claveproductopartida = ClaveProdServ::where('Clave', $df->ClaveProducto)->first();
                $claveunidadpartida = ClaveUnidad::where('Clave', $df->ClaveUnidad)->first();
                $claveproducto = $claveproductopartida ? $claveproductopartida->Clave : '';
                $nombreclaveproducto = $claveproductopartida ? $claveproductopartida->Nombre : '';
                $claveunidad = $claveunidadpartida ? $claveunidadpartida->Clave : '';
                $nombreclaveunidad = $claveunidadpartida ? $claveunidadpartida->Nombre : '';
                $filasdetallesfactura= $filasdetallesfactura.
                '<tr class="filasproductos" id="filaproducto'.$contadorfilas.'">'.
                    '<td class="tdmod"><div class="numeropartida">'.$partida.'</div><input type="hidden" class="form-control itempartida" name="itempartida[]" value="'.$df->Item.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control codigopartida" name="codigopartida[]" value="'.$df->Codigo.'" readonly data-parsley-length="[1, 20]"><b style="font-size:12px;">'.$df->Codigo.'</b></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodxl descripcionpartida" name="descripcionpartida[]" value="'.htmlspecialchars($df->Descripcion, ENT_QUOTES).'" required '.$readonly.' data-parsley-length="[1, 255]" onkeyup="tipoLetra(this);"></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodxs unidadpartida" name="unidadpartida[]" value="'.$df->Unidad.'" required data-parsley-length="[1, 5]">'.$df->Unidad.'</td>'.
                    '<td class="tdmod">'.
                        '<input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm cantidadpartida" name="cantidadpartida[]" value="'.Helpers::convertirvalorcorrecto($df->Cantidad).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" '.$readonly.'>'.
                        '<input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm cantidadpartidadb" name="cantidadpartidadb[]" value="'.Helpers::convertirvalorcorrecto($df->Cantidad).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly>'.
                        '<input type="hidden" class="form-control cantidadincorrecta" name="cantidadincorrecta[]" >'.
                        '<input type="hidden" class="form-control realizarbusquedaexistencias" name="realizarbusquedaexistencias[]" value="1" >'.
                        '<div class="cantidaderrorexistencias" style="color:#dc3545;font-size:9px; display:none"></div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm preciopartida" name="preciopartida[]" value="'.Helpers::convertirvalorcorrecto($df->Precio).'" data-parsley-min="0.'.$this->numerocerosconfiguradosinputnumberstep.'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importepartida" name="importepartida[]" value="'.Helpers::convertirvalorcorrecto($df->Importe).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentoporcentajepartida" name="descuentoporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($df->Dcto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm descuentopesospartida" name="descuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($df->Descuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm importedescuentopesospartida" name="importedescuentopesospartida[]" value="'.Helpers::convertirvalorcorrecto($df->ImporteDescuento).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm subtotalpartida" name="subtotalpartida[]" value="'.Helpers::convertirvalorcorrecto($df->SubTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm ivaporcentajepartida" name="ivaporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($df->Impuesto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calculartotalesfilas('.$contadorfilas.');" '.$readonly.'></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm trasladoivapesospartida" name="trasladoivapesospartida[]" value="'.Helpers::convertirvalorcorrecto($df->Iva).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm totalpesospartida" name="totalpesospartida[]" value="'.Helpers::convertirvalorcorrecto($df->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costopartida" name="costopartida[]" value="'.Helpers::convertirvalorcorrecto($df->Costo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costototalpartida" name="costototalpartida[]" value="'.Helpers::convertirvalorcorrecto($df->CostoTotal).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control inputnextdet divorinputmodsm comisionporcentajepartida" name="comisionporcentajepartida[]" value="'.Helpers::convertirvalorcorrecto($df->Com).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm comisionpesospartida" name="comisionpesospartida[]" value="'.Helpers::convertirvalorcorrecto($df->Comision).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadpartida" name="utilidadpartida[]" value="'.Helpers::convertirvalorcorrecto($df->Utilidad).'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm remisionpartida" name="remisionpartida[]"  value="'.$df->Remision.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cartaportepartida" name="cartaportepartida[]"  value="" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm ordenpartida" name="ordenpartida[]"  value="'.$df->Orden.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm departamentopartida" name="departamentopartida[]"  value="'.$df->Departamento.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm cargopartida" name="cargopartida[]"  value="'.$df->Cargo.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm partida" name="partida[]" value="'.$df->Partida.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm tiendapartida" name="tiendapartida[]"  value="'.$df->Tienda.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm pedidopartida" name="pedidopartida[]"  value="'.$df->Pedido.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm almacenpartida" name="almacenpartida[]"  value="'.$df->Almacen.'" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control inputnextdet divorinputmodsm datosunidadpartida" name="datosunidadpartida[]"  value="'.$df->DatosUnidad.'" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadaritmeticapartida" name="utilidadaritmeticapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod" hidden><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm utilidadfinancieriapartida" name="utilidadfinancieriapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodsm monedapartida" name="monedapartida[]" value="'.$df->Moneda.'" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm costodelistapartida" name="costodelistapartida[]" value="'.Helpers::convertirvalorcorrecto($df->CostoDeLista).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodsm tipocambiopartida" name="tipocambiopartida[]" value="'.Helpers::convertirvalorcorrecto($df->TipoDeCambio).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodxl">'.
                            '<div class="col-xs-2 col-sm-2 col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect btnlistarclavesproductos" data-toggle="tooltip" title="Ver Claves Productos o Servicios" onclick="listarclavesproductos('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-xs-10 col-sm-10 col-md-10">'.
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveproductopartida" name="claveproductopartida[]"  value="'.$claveproducto.'" readonly required data-parsley-length="[1, 20]">'.
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
                                '<input type="text" class="form-control inputnextdet divorinputmodsm claveunidadpartida" name="claveunidadpartida[]"  value="'.$claveunidad.'" readonly required data-parsley-length="[1, 5]">'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="text" class="form-control divorinputmodmd nombreclaveunidadpartida" name="nombreclaveunidadpartida[]"  value="'.$nombreclaveunidad.'" readonly></td>'.
                '</tr>';
                if($df->Remision != null){
                    array_push($consarrayremisiones, $df->Remision);
                }
                if($df->Orden != null){
                    array_push($consarrayordenes, $df->Orden);
                }
                $contadorproductos++;
                $contadorfilas++;
            }
        }
        //factura documentos
        $documentosfactura = FacturaDocumento::where('Factura', $request->facturamodificar)->get();
        $numerodocumentosfactura = FacturaDocumento::where('Factura', $request->facturamodificar)->count();
        $filasdocumentosfactura = '';
        if($numerodocumentosfactura > 0){
            foreach($documentosfactura as $docf){
                    $filasdocumentosfactura= $filasdocumentosfactura.
                    '<tr class="filasuuid" id="filauuid0">'.
                        '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminaruuid" onclick="eliminarfilauuid(0)">X</div><input type="hidden" class="form-control uuidagregadoen" name="uuidagregadoen[]" value="NA" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm uuidrelacionadobd" name="uuidrelacionadobd[]" value="'.$docf->UUID.'" readonly><input type="hidden" class="form-control divorinputmodsm uuidrelacionado" name="uuidrelacionado[]" value="'.$docf->UUID.'" readonly>'.$docf->UUID.'</td>'.
                    '</tr>';
            }
        }
        //array remisiones o ordenes
        if(sizeof($consarrayremisiones) > 0){
            $arrayrem = array_unique($consarrayremisiones);
            foreach($arrayrem as $val){
                array_push($arrayremisiones, $val);
            }
        }
        if(sizeof($consarrayordenes) > 0){
            $arrayord = array_unique($consarrayordenes);
            foreach($arrayord as $val){
                array_push($arrayordenes, $val);
            }
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($factura->Status != 'POR COBRAR'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
            }
        }
        if(Auth::user()->role_id != 1){
            if($factura->Status != 'POR COBRAR'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($factura->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        $data = array(
            "factura" => $factura,
            "filasdetallesfactura" => $filasdetallesfactura,
            "numerodetallesfactura" => $numerodetallesfactura,
            "arrayremisiones" => $arrayremisiones,
            "arrayordenes" => $arrayordenes,
            "contadorproductos" => $contadorproductos,
            "contadorfilas" => $contadorfilas,
            "partida" => $partida,
            "cliente" => $cliente,
            "agente" => $agente,
            "regimenfiscal" => $regimenfiscal,
            "nombretiporelacion" => $nombretiporelacion,
            "clavetiporelacion" => $clavetiporelacion,
            "formapago" => $formapago,
            "metodopago" => $metodopago,
            "usocfdi" => $usocfdi,
            "residenciafiscal" => $residenciafiscal,
            "regimenfiscalreceptor" => $regimenfiscalreceptor,
            "periodicidad" => $periodicidad,
            "meses" => $meses,
            "filasdocumentosfactura" => $filasdocumentosfactura,
            "numerodocumentosfactura" => $numerodocumentosfactura,
            "fecha" => Helpers::formatoinputdatetime($factura->Fecha),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($factura->Fecha),
            "importe" => Helpers::convertirvalorcorrecto($factura->Importe),
            "descuento" => Helpers::convertirvalorcorrecto($factura->Descuento),
            "subtotal" => Helpers::convertirvalorcorrecto($factura->SubTotal),
            "iva" => Helpers::convertirvalorcorrecto($factura->Iva),
            "total" => Helpers::convertirvalorcorrecto($factura->Total),
            "tipocambio" => Helpers::convertirvalorcorrecto($factura->TipoCambio),
            "credito" => Helpers::convertirvalorcorrecto($cliente->Credito),
            "saldo" => Helpers::convertirvalorcorrecto($cliente->Saldo),
            "utilidad" => Helpers::convertirvalorcorrecto($factura->Utilidad),
            "costo" => Helpers::convertirvalorcorrecto($factura->Costo),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }

    //cambios
    public function facturas_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        $factura = $request->facturabd;
        $Factura = Factura::where('Factura', $factura)->first();
        //array detalles documentos antes de modificacion
        $ArrayDetallesDocumentosFacturaAnterior = Array();
        $DetallesDocumentosFacturaAnterior = FacturaDocumento::where('Factura', $factura)->get();
        foreach($DetallesDocumentosFacturaAnterior as $detalledocumento){
            array_push($ArrayDetallesDocumentosFacturaAnterior, $detalledocumento->Factura.'#'.$detalledocumento->UUID);
        }
        //array detalles documentos despues de modificacion
        $ArrayDetallesDocumentosFacturaNuevo = Array();
        if($request->numerofilasuuid > 0){
            foreach ($request->uuidrelacionado as $key => $nuevodocumento){
                if($request->uuidagregadoen [$key] == 'NA'){
                    array_push($ArrayDetallesDocumentosFacturaNuevo, $factura.'#'.$nuevodocumento);
                }
            }
        }
        //diferencias entre arreglos
        $diferencias_arreglos = array_diff($ArrayDetallesDocumentosFacturaAnterior, $ArrayDetallesDocumentosFacturaNuevo);
        //iteramos las diferencias entre arreglos
        if(count($diferencias_arreglos) > 0){
            foreach($diferencias_arreglos as $eliminapartida){
                $explode_d = explode("#",$eliminapartida);
                //eliminar detalle
                $eliminardetalledocumento= FacturaDocumento::where('Factura', $explode_d[0])->where('UUID', $explode_d[1])->forceDelete();
            }
        }
        //modificar
        Factura::where('Factura', $factura)
        ->update([
            'Agente' => $request->numeroagente,
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Hora' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Plazo' => $request->plazo,
            'Pedido' => $request->pedido,
            'Tipo' => $request->tipo,
            'Unidad' => $request->tipounidad,
            'Moneda' => $request->moneda,
            'TipoCambio' => $request->pesosmoneda,
            'Obs' => $request->observaciones,
            'Descripcion' => $request->descripcion,
            'CondicionesDePago' => $request->condicionesdepago,
            'LugarExpedicion' => $request->lugarexpedicion,
            'RegimenFiscal' => $request->claveregimenfiscal,
            'TipoRelacion' => $request->clavetiporelacion,
            'Confirmacion' => $request->confirmacion,
            'FormaPago' => $request->claveformapago,
            'MetodoPago' => $request->clavemetodopago,
            'UsoCfdi' => $request->claveusocfdi,
            'ResidenciaFiscal' => $request->claveresidenciafiscal,
            'RegimenFiscalReceptor' => $request->claveregimenfiscalreceptor,
            'Periodicidad'=> $request->claveperiodicidad,
            'Meses' => $request->clavemes,
            'NumRegIdTrib' => $request->numeroregidtrib
        ]);
        //ver si se puede modificar la factura siempre y cuando sea una factura libre y no tenga ningun documento ligado a ella
        switch($Factura->Serie){
            case "LST":
                //modificar saldo cliente
                $cliente = Cliente::where('Numero', $Factura->Cliente)->first();
                $NuevoSaldoCliente = $cliente->Saldo + $Factura->Total - $request->total;
                Cliente::where('Numero', $Factura->Cliente)
                ->update([
                    'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldoCliente)
                ]);
                //modificar detalle
                Factura::where('Factura', $factura)
                ->update([
                    'Importe' => $request->importe,
                    'Descuento' => $request->descuento,
                    'SubTotal' => $request->subtotal,
                    'Iva' => $request->iva,
                    'Total' => $request->total,
                    'Costo' => $request->costo,
                    'Comision' => $request->comision,
                    'Utilidad' => $request->utilidad,
                    'Saldo' => $request->total
                ]);
                break;
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "FACTURAS";
        $BitacoraDocumento->Movimiento = $factura;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Factura->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //detalles
        foreach ($request->codigopartida as $key => $codigopartida){
            //ver si se puede modificar la factura siempre y cuando sea una factura libre y no tenga ningun documento ligado a ella
            switch($Factura->Serie){
                case "LST":
                    //modificar detalle
                    FacturaDetalle::where('Factura', $factura)
                    ->where('Item', $request->itempartida [$key])
                    ->update([
                        'Descripcion' => $request->descripcionpartida [$key],
                        'Cantidad' =>  $request->cantidadpartida  [$key],
                        'Precio' =>  $request->preciopartida [$key],
                        'Importe' => $request->importepartida [$key],
                        'Dcto' => $request->descuentoporcentajepartida [$key],
                        'Descuento' => $request->descuentopesospartida [$key],
                        'ImporteDescuento' => $request->importedescuentopesospartida [$key],
                        'SubTotal' => $request->subtotalpartida [$key],
                        'Impuesto' => $request->ivaporcentajepartida [$key],
                        'Iva' => $request->trasladoivapesospartida [$key],
                        'Total' => $request->totalpesospartida [$key],
                        'Costo' => $request->costopartida [$key],
                        'CostoTotal' => $request->costototalpartida [$key],
                        'Com' => $request->comisionporcentajepartida [$key],
                        'Comision' => $request->comisionpesospartida [$key],
                        'Utilidad' => $request->utilidadpartida [$key],
                        'ClaveProducto' => $request->claveproductopartida [$key],
                        'ClaveUnidad' => $request->claveunidadpartida [$key],
                    ]);
                    break;
            }
        }
        //detalles documentos
        if($request->numerofilasuuid > 0){
            foreach ($request->uuidrelacionado as $key => $uuidrelacionado){
                //if la partida se agrego en la modificacion se realiza un insert
                if($request->uuidagregadoen [$key] != 'NA'){
                    $FacturaDocumento=new FacturaDocumento;
                    $FacturaDocumento->Factura = $factura;
                    $FacturaDocumento->UUID = $uuidrelacionado;
                    $FacturaDocumento->save();
                }
            }
        }
        return response()->json($Factura);
    }

    //obtener datos generales
    public function facturas_obtener_datos_generales(Request $request){
        $documento = Factura::where('Factura', $request->Factura)->first();
        return response()->json($documento);
    }

    //guardar cambios datos generales
    public function facturas_guardar_modificacion_datos_generales(Request $request){
        $Factura = Factura::where('Factura', $request->facturadatosgenerales)->first();
        Factura::where('Factura', $request->facturadatosgenerales)
        ->update([
            'Pedido'=>$request->pedidodatosgenerales,
        ]);
        return response()->json($Factura);
    }

    //obtener movimientos factura
    public function facturas_obtener_kardex(Request $request){
        $kardex = DB::select('exec ObtenerKardexFactura ?', array($request->factura));
        $numerodecimalesconfigurados = config('app.numerodedecimales');
        $filasmovimientos = "";
        $fila = 1;
        foreach($kardex as $d){
            $colorfila = '';
            if($d->status == 'BAJA'){
                $colorfila = 'bg-red';
            }
            $filasmovimientos= $filasmovimientos.
            '<tr class="'.$colorfila.'">'.
                '<td><b>'.$fila.'</b></td>'.
                '<td>'.$d->Movimiento.'</td>'.
                '<td>'.$d->pago.'</td>'.
                '<td>'.$d->fecha.'</td>'.
                '<td>'.Helpers::convertirvalorcorrecto($d->abono).'</td>'.
                '<td>'.$d->status.'</td>'.
            '</tr>';
            $fila++;
        }
        $data = array(
            'filasmovimientos' => $filasmovimientos,
        );
        return response()->json($data);
    }

    //verificar si continua baja
    public function facturas_verificar_si_continua_baja(Request $request){
        $errores = '';
        $Factura = Factura::where('Factura', $request->facturadesactivar)->first();
        $numerocuentasporcobrar = CuentaXCobrarDetalle::where('Factura', $request->facturadesactivar)->Where('Abono', '>', 0)->count();
        $numeronotascliente = NotaClienteDocumento::where('Factura', $request->facturadesactivar)->where('Descuento', '>', 0)->count();
        $numerocuentaxcobrar = 0;
        $numeronotacliente = 0;
        //verificar si hay una cuenta por cobrar ligada
        if($numerocuentasporcobrar > 0){
            $detallecuentaxcobrar = CuentaXCobrarDetalle::where('Factura', $request->facturadesactivar)->Where('Abono', '>', 0)->first();
            $numerocuentaxcobrar = $detallecuentaxcobrar->Pago;
        }
        //verificar si hay una nota de credito cliente ligada
        if($numeronotascliente > 0){
            $detallenotacliente = NotaClienteDocumento::where('Factura', $request->facturadesactivar)->where('Descuento', '>', 0)->first();
            $numeronotacliente = $detallenotacliente->Nota;
        }
        $resultadofechas = Helpers::compararanoymesfechas($Factura->Fecha);
        $data = array(
            'numerocuentasporcobrar' => $numerocuentasporcobrar,
            'numerocuentaxcobrar' => $numerocuentaxcobrar,
            'numeronotascliente' => $numeronotascliente,
            'numeronotacliente' => $numeronotacliente,
            'resultadofechas' => $resultadofechas,
            'errores' => $errores,
            'Status' => $Factura->Status
        );
        return response()->json($data);
    }

    //bajas
    public function facturas_alta_o_baja(Request $request){
        $Factura = Factura::where('Factura', $request->facturadesactivar)->first();
        //Modificar saldo cliente
        $cliente = Cliente::where('Numero', $Factura->Cliente)->first();
        //regresar saldo restar
        $saldoanterior = $cliente->Saldo - $Factura->Total;
        Cliente::where('Numero', $Factura->Cliente)
                            ->update([
                                'Saldo' => Helpers::convertirvalorcorrecto($saldoanterior)
                            ]);
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        Factura::where('Factura', $request->facturadesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
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
                    'Costo' => '0.000000',
                    'Comision' => '0.000000',
                    'Utilidad' => '0.000000',
                    'Abonos' => '0.000000',
                    'Descuentos' => '0.000000',
                    'Saldo' => '0.000000'
                ]);
        $detalles = FacturaDetalle::where('Factura', $request->facturadesactivar)->get();
        // detalles
        foreach($detalles as $detalle){
            //regresar status de remision o orden
            switch ($Factura->Depto) {
                case "SERVICIO":
                    OrdenTrabajo::where('Orden', $detalle->Orden)
                                ->update([
                                    'Status' => 'ABIERTA'
                                ]);
                    OrdenTrabajoDetalle::where('Orden', $detalle->Orden)
                            ->where('Partida', $detalle->Partida)
                            ->update([
                                'Status' => ''
                            ]);
                    break;
                case "PRODUCTOS":
                    Remision::where('Remision', $detalle->Remision)
                            ->update([
                                'Status' => 'POR FACTURAR'
                            ]);
                    break;
            }
            //colocar en ceros cantidades detalles
            FacturaDetalle::where('Factura', $request->facturadesactivar)
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
                                'CostoTotal' => '0.000000',
                                'Com' => '0.000000',
                                'Comision' => '0.000000',
                                'Utilidad' => '0.000000',
                                'Remision' => '',
                                'Orden' => '',
                                'Departamento' => '',
                                'Cargo' => '',
                                'Almacen' => '0'
                            ]);
        }
        //eliminar detalles documentos
        $detallesdocumentos = FacturaDocumento::where('Factura', $request->facturadesactivar)->forceDelete();
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "FACTURAS";
        $BitacoraDocumento->Movimiento = $request->facturadesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $Factura->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($Factura);
    }

    //buscar folio on key up
    public function facturas_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaFactura::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Total', function($data){
                    $total = Helpers::convertirvalorcorrecto($data->Total);
                    return $total;
                })
                ->make(true);
        }
    }

    //generacion de formato en PDF
    public function facturas_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos xml generados
        Helpers::eliminararchivosxmlsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $facturas = Factura::where('Factura', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $facturas = Factura::whereIn('Factura', $request->arraypdf)->orderBy('Folio', 'ASC')->take(250)->get();
            }else{
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $facturas = Factura::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(250)->get();
                }else{
                    $facturas = Factura::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(250)->get();
                }
            }
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfiles = array();
        $arrayfilespdf = array();
        foreach ($facturas as $f){
            $data=array();
            $facturadetalles = FacturaDetalle::where('Factura', $f->Factura)->get();
            $arraytipofactura=array();
            $tipodetalles = '';
            foreach($facturadetalles as $fd){
                if($fd->Facturar == 'PRODUCTOS'){
                    array_push($arraytipofactura, $fd->Remision);
                    $tipodetalles = 'remisiones';
                }else if($fd->Facturar == 'SERVICIO'){
                    array_push($arraytipofactura, $fd->Orden);
                    $tipodetalles = 'ordenes';
                }else if($fd->Facturar == 'LIBRE'){
                    $tipodetalles = 'libre';
                }
            }
            //if la factura es para remisiones o ordenes
            if(sizeof($arraytipofactura) > 0){
                $serviciosoremisionesfactura = array_unique($arraytipofactura);
                sort($serviciosoremisionesfactura, SORT_NATURAL | SORT_FLAG_CASE);
                $datageneral = array();
                foreach($serviciosoremisionesfactura as $sorf){
                    $datadetalle=array();
                    switch ($tipodetalles) {
                        case 'remisiones':
                            $datosgenerales = Remision::where('Remision', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observacionesdocumento = $datosgenerales->Obs;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Remision', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $insumodet = RemisionDetalle::where('Remision', $dfr->Remision)->where('Codigo', $dfr->Codigo)->first();
                                if($insumodet == null){
                                    $insumodetalle = "";
                                }else{
                                    $insumodetalle = $insumodet->Insumo;
                                }
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "insumodetalle"=>$insumodetalle,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                        case 'ordenes':
                            $datosgenerales = OrdenTrabajo::where('Orden', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observaciones = $datosgenerales->ObsOrden;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Orden', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                    }
                    //obtener XML
                    if($request->descargar_xml == 1){
                        $factura = Factura::where('Factura', $f->Factura)->first();
                        $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->where('IdFacturapi', '<>', NULL)->first();
                        if($comprobante != null){
                            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
                            $nombre_xml = "FacturaNo".$f->Factura.'##'.$f->UUID.'.xml';
                            Storage::disk('local2')->put($nombre_xml, $descargar_xml);
                            array_push($arrayfiles, $nombre_xml);
                        }
                    }
                    $datageneral[]=array(
                        "datosgenerales"=>$datosgenerales,
                        "datadetalle" => $datadetalle,
                        "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                        "obsdocumento" => $observacionesdocumento
                    );
                }
            //si la factura es libre
            }else{
                $datageneral = array();
                $datadetalle=array();
                $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->get();
                $sumatotaldetalles = 0;
                foreach($detallesfacturaremision as $dfr){
                    $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                    $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                    $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                    $datadetalle[]=array(
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                        "codigodetalle"=>$dfr->Codigo,
                        "descripciondetalle"=>$dfr->Descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                        "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                        "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                        "claveproducto" => $claveproducto,
                        "claveunidad" => $claveunidad
                    );
                }
                $datageneral[]=array(
                    "datadetalle" => $datadetalle,
                    "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                    "obsdocumento" => ""
                );
            }
            $cliente = Cliente::where('Numero', $f->Cliente)->first();
            $estadoclienteexiste = Estado::where('Clave', $cliente->Estado)->count();
            if($estadoclienteexiste > 0){
                $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
                $est = $estadocliente->Nombre;
            }else{
                $est = $cliente->Estado;
            }
            //$regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cliente->RegimenFiscal)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $f->RegimenFiscalReceptor)->first();
            $contaragente = Agente::where('Numero', $f->Agente)->count();
            $NombreAgente = "";
            if($contaragente > 0){
                $agente = Agente::where('Numero', $f->Agente)->first();
                $NombreAgente = $agente->Nombre;
            }
            $formapago = FormaPago::where('Clave', $f->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $f->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $f->UsoCfdi)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $f->RegimenFiscal)->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($f->Total, 2, 'M.N.');
            $documentosrelacionados = FacturaDocumento::where('Factura', $f->Factura)->first();
            //dd($documentosrelacionados);
            $foliofiscalfactura = FolioComprobanteFactura::where('Serie', $f->Serie)->where('Esquema', $f->Esquema)->first();
            $pagares = $foliofiscalfactura->Pagare;
            $reemplazarbeneficiario = str_replace("%beneficiario", $this->empresa->Empresa, $pagares);
            $reemplazarvence = str_replace("%vence", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()), $reemplazarbeneficiario);
            $reemplazartotal = str_replace("%total", Helpers::convertirvalorcorrecto($f->Total), $reemplazarvence);
            $reemplazartotalletra = str_replace("%letratotal", $totalletras, $reemplazartotal);
            $reemplazarortorgante = str_replace("%nombre", $cliente->Nombre.' ('.$cliente->Numero.')', $reemplazartotalletra);
            $reemplazardomicilio = str_replace("%direccion", $cliente->Calle.' '.$cliente->noExterior.' '.$cliente->noInterior.' Colonia: '.$cliente->Colonia.' Estado: '.$cliente->Localidad, $reemplazarortorgante);
            $reemplazarciudad = str_replace("%ciudad", $cliente->Municipio.' C.P. '.$cliente->CodigoPostal, $reemplazardomicilio);
            $reemplazarestado = str_replace("%estadobeneficiario", $this->empresa->Estado, $reemplazarciudad);
            $reemplazarfecha = str_replace("%fecha", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->toDateTimeString()), $reemplazarestado);
            $reemplazarbr = str_replace("%br", "\n\n", $reemplazarfecha);
            $pagare = $reemplazarbr;
            $data[]=array(
                "factura"=>$f,
                "documentosrelacionados" => $documentosrelacionados,
                "datageneral" => $datageneral,
                "cliente" => $cliente,
                "regimenfiscalcliente" => $regimenfiscalcliente,
                "est" => $est,
                "NombreAgente" => $NombreAgente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "pagare"=> $pagare,
                "tipodetalles" => $tipodetalles,
                "fechavence" => Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString(),
                "subtotalfactura"=>Helpers::convertirvalorcorrecto($f->SubTotal),
                "ivafactura"=>Helpers::convertirvalorcorrecto($f->Iva),
                "totalfactura"=>Helpers::convertirvalorcorrecto($f->Total),
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($f->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.facturas.formato_pdf_facturas', compact('data'))
            ->setPaper('Letter')
            //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$f->Factura.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($facturas as $fac){
            $ArchivoPDF = "PDF".$fac->Factura.".pdf";
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
                $pdfMerger->save("Facturas.pdf", "browser");//mostrarlos en el navegador
            }else{
                //carpeta donde se guardara el archivo zip
                $public_dir=public_path();
                // Zip File Name
                $zipFileName = 'Facturas.zip';
                // Crear Objeto ZipArchive
                $zip = new ZipArchive;
                if ($zip->open($public_dir . '/xml_descargados/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
                    // Agregar archivos que se comprimiran
                    foreach($arrayfiles as $af) {
                        $zip->addFile(Storage::disk('local2')->getAdapter()->applyPathPrefix($af),$af);
                    }
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
    //generacion de formato en PDF INTERNO
    public function facturas_generar_pdfs_indiv($documento){
        $facturas = Factura::where('Factura', $documento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($facturas as $f){
            $facturadetalles = FacturaDetalle::where('Factura', $f->Factura)->get();
            $arraytipofactura=array();
            $tipodetalles = '';
            foreach($facturadetalles as $fd){
                if($fd->Facturar == 'PRODUCTOS'){
                    array_push($arraytipofactura, $fd->Remision);
                    $tipodetalles = 'remisiones';
                }else if($fd->Facturar == 'SERVICIO'){
                    array_push($arraytipofactura, $fd->Orden);
                    $tipodetalles = 'ordenes';
                }else if($fd->Facturar == 'LIBRE'){
                    $tipodetalles = 'libre';
                }
            }
            //if la factura es para remisiones o ordenes
            if(sizeof($arraytipofactura) > 0){
                $serviciosoremisionesfactura = array_unique($arraytipofactura);
                sort($serviciosoremisionesfactura, SORT_NATURAL | SORT_FLAG_CASE);
                $datageneral = array();
                foreach($serviciosoremisionesfactura as $sorf){
                    $datadetalle=array();
                    switch ($tipodetalles) {
                        case 'remisiones':
                            $datosgenerales = Remision::where('Remision', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observacionesdocumento = $datosgenerales->Obs;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Remision', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $insumodet = RemisionDetalle::where('Remision', $dfr->Remision)->where('Codigo', $dfr->Codigo)->first();
                                if($insumodet == null){
                                    $insumodetalle = "";
                                }else{
                                    $insumodetalle = $insumodet->Insumo;
                                }
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "insumodetalle"=>$insumodetalle,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                        case 'ordenes':
                            $datosgenerales = OrdenTrabajo::where('Orden', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observaciones = $datosgenerales->ObsOrden;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Orden', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                    }
                    $datageneral[]=array(
                        "datosgenerales"=>$datosgenerales,
                        "datadetalle" => $datadetalle,
                        "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                        "obsdocumento" => $observacionesdocumento
                    );
                }
            //si la factura es libre
            }else{
                $datageneral = array();
                $datadetalle=array();
                $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->get();
                $sumatotaldetalles = 0;
                foreach($detallesfacturaremision as $dfr){
                    $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                    $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                    $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                    $datadetalle[]=array(
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                        "codigodetalle"=>$dfr->Codigo,
                        "descripciondetalle"=>$dfr->Descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                        "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                        "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                        "claveproducto" => $claveproducto,
                        "claveunidad" => $claveunidad
                    );
                }
                $datageneral[]=array(
                    "datadetalle" => $datadetalle,
                    "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                    "obsdocumento" => "",
                );
            }
            $cliente = Cliente::where('Numero', $f->Cliente)->first();
            $estadoclienteexiste = Estado::where('Clave', $cliente->Estado)->count();
            if($estadoclienteexiste > 0){
                $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
                $est = $estadocliente->Nombre;
            }else{
                $est = $cliente->Estado;
            }
            //$regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cliente->RegimenFiscal)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $f->RegimenFiscalReceptor)->first();
            $contaragente = Agente::where('Numero', $f->Agente)->count();
            $NombreAgente = "";
            if($contaragente > 0){
                $agente = Agente::where('Numero', $f->Agente)->first();
                $NombreAgente = $agente->Nombre;
            }
            $formapago = FormaPago::where('Clave', $f->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $f->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $f->UsoCfdi)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $f->RegimenFiscal)->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($f->Total, 2, 'M.N.');
            $documentosrelacionados = FacturaDocumento::where('Factura', $f->Factura)->first();
            //dd($documentosrelacionados);
            $foliofiscalfactura = FolioComprobanteFactura::where('Serie', $f->Serie)->where('Esquema', $f->Esquema)->first();
            $pagares = $foliofiscalfactura->Pagare;
            $reemplazarbeneficiario = str_replace("%beneficiario", $this->empresa->Empresa, $pagares);
            $reemplazarvence = str_replace("%vence", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()), $reemplazarbeneficiario);
            $reemplazartotal = str_replace("%total", Helpers::convertirvalorcorrecto($f->Total), $reemplazarvence);
            $reemplazartotalletra = str_replace("%letratotal", $totalletras, $reemplazartotal);
            $reemplazarortorgante = str_replace("%nombre", $cliente->Nombre.' ('.$cliente->Numero.')', $reemplazartotalletra);
            $reemplazardomicilio = str_replace("%direccion", $cliente->Calle.' '.$cliente->noExterior.' '.$cliente->noInterior.' Colonia: '.$cliente->Colonia.' Estado: '.$cliente->Localidad, $reemplazarortorgante);
            $reemplazarciudad = str_replace("%ciudad", $cliente->Municipio.' C.P. '.$cliente->CodigoPostal, $reemplazardomicilio);
            $reemplazarestado = str_replace("%estadobeneficiario", $this->empresa->Estado, $reemplazarciudad);
            $reemplazarfecha = str_replace("%fecha", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->toDateTimeString()), $reemplazarestado);
            $reemplazarbr = str_replace("%br", "\n\n", $reemplazarfecha);
            $pagare = $reemplazarbr;
            $data[]=array(
                "factura"=>$f,
                "documentosrelacionados"=>$documentosrelacionados,
                "datageneral" => $datageneral,
                "cliente" => $cliente,
                "regimenfiscalcliente" => $regimenfiscalcliente,
                "est" => $est,
                "NombreAgente" => $NombreAgente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "pagare"=> $pagare,
                "tipodetalles" => $tipodetalles,
                "fechavence" => Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString(),
                "subtotalfactura"=>Helpers::convertirvalorcorrecto($f->SubTotal),
                "ivafactura"=>Helpers::convertirvalorcorrecto($f->Iva),
                "totalfactura"=>Helpers::convertirvalorcorrecto($f->Total),
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($f->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.facturas.formato_pdf_facturas', compact('data'))
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
    public function facturas_obtener_datos_envio_email(Request $request){
        $factura = Factura::where('Factura', $request->documento)->first();
        $cliente = Cliente::where('Numero',$factura->Cliente)->first();
        $email2cc = '';
        $email3cc = '';
        if($cliente->Email2 != '' || $cliente->Email2 != null){
            $email2cc = $cliente->Email2;
        }
        if($cliente->Email3 != '' || $cliente->Email3 != null){
            $email3cc = $cliente->Email3;
        }
        $data = array(
            'factura' => $factura,
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
    public function facturas_enviar_pdfs_email(Request $request){
        $facturas = Factura::where('Factura', $request->emaildocumento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($facturas as $f){
            $facturadetalles = FacturaDetalle::where('Factura', $f->Factura)->get();
            $arraytipofactura=array();
            $tipodetalles = '';
            foreach($facturadetalles as $fd){
                if($fd->Facturar == 'PRODUCTOS'){
                    array_push($arraytipofactura, $fd->Remision);
                    $tipodetalles = 'remisiones';
                }else if($fd->Facturar == 'SERVICIO'){
                    array_push($arraytipofactura, $fd->Orden);
                    $tipodetalles = 'ordenes';
                }else if($fd->Facturar == 'LIBRE'){
                    $tipodetalles = 'libre';
                }
            }
            //if la factura es para remisiones o ordenes
            if(sizeof($arraytipofactura) > 0){
                $serviciosoremisionesfactura = array_unique($arraytipofactura);
                sort($serviciosoremisionesfactura, SORT_NATURAL | SORT_FLAG_CASE);
                $datageneral = array();
                foreach($serviciosoremisionesfactura as $sorf){
                    $datadetalle=array();
                    switch ($tipodetalles) {
                        case 'remisiones':
                            $datosgenerales = Remision::where('Remision', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observacionesdocumento = $datosgenerales->Obs;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Remision', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $insumodet = RemisionDetalle::where('Remision', $dfr->Remision)->where('Codigo', $dfr->Codigo)->first();
                                if($insumodet == null){
                                    $insumodetalle = "";
                                }else{
                                    $insumodetalle = $insumodet->Insumo;
                                }
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "insumodetalle"=>$insumodetalle,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                        case 'ordenes':
                            $datosgenerales = OrdenTrabajo::where('Orden', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observaciones = $datosgenerales->ObsOrden;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Orden', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                    }
                    $datageneral[]=array(
                        "datosgenerales"=>$datosgenerales,
                        "datadetalle" => $datadetalle,
                        "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                        "obsdocumento" => $observacionesdocumento
                    );
                }
            //si la factura es libre
            }else{
                $datageneral = array();
                $datadetalle=array();
                $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->get();
                $sumatotaldetalles = 0;
                foreach($detallesfacturaremision as $dfr){
                    $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                    $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                    $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                    $datadetalle[]=array(
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                        "codigodetalle"=>$dfr->Codigo,
                        "descripciondetalle"=>$dfr->Descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                        "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                        "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                        "claveproducto" => $claveproducto,
                        "claveunidad" => $claveunidad
                    );
                }
                $datageneral[]=array(
                    "datadetalle" => $datadetalle,
                    "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                    "obsdocumento" => ""
                );
            }
            $cliente = Cliente::where('Numero', $f->Cliente)->first();
            $estadoclienteexiste = Estado::where('Clave', $cliente->Estado)->count();
            if($estadoclienteexiste > 0){
                $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
                $est = $estadocliente->Nombre;
            }else{
                $est = $cliente->Estado;
            }
            //$regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cliente->RegimenFiscal)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $f->RegimenFiscalReceptor)->first();
            $contaragente = Agente::where('Numero', $f->Agente)->count();
            $NombreAgente = "";
            if($contaragente > 0){
                $agente = Agente::where('Numero', $f->Agente)->first();
                $NombreAgente = $agente->Nombre;
            }
            $formapago = FormaPago::where('Clave', $f->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $f->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $f->UsoCfdi)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $f->RegimenFiscal)->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($f->Total, 2, 'M.N.');
            $documentosrelacionados = FacturaDocumento::where('Factura', $f->Factura)->first();
            //dd($documentosrelacionados);
            $foliofiscalfactura = FolioComprobanteFactura::where('Serie', $f->Serie)->where('Esquema', $f->Esquema)->first();
            $pagares = $foliofiscalfactura->Pagare;
            $reemplazarbeneficiario = str_replace("%beneficiario", $this->empresa->Empresa, $pagares);
            $reemplazarvence = str_replace("%vence", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()), $reemplazarbeneficiario);
            $reemplazartotal = str_replace("%total", Helpers::convertirvalorcorrecto($f->Total), $reemplazarvence);
            $reemplazartotalletra = str_replace("%letratotal", $totalletras, $reemplazartotal);
            $reemplazarortorgante = str_replace("%nombre", $cliente->Nombre.' ('.$cliente->Numero.')', $reemplazartotalletra);
            $reemplazardomicilio = str_replace("%direccion", $cliente->Calle.' '.$cliente->noExterior.' '.$cliente->noInterior.' Colonia: '.$cliente->Colonia.' Estado: '.$cliente->Localidad, $reemplazarortorgante);
            $reemplazarciudad = str_replace("%ciudad", $cliente->Municipio.' C.P. '.$cliente->CodigoPostal, $reemplazardomicilio);
            $reemplazarestado = str_replace("%estadobeneficiario", $this->empresa->Estado, $reemplazarciudad);
            $reemplazarfecha = str_replace("%fecha", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->toDateTimeString()), $reemplazarestado);
            $reemplazarbr = str_replace("%br", "\n\n", $reemplazarfecha);
            $pagare = $reemplazarbr;
            $data[]=array(
                "factura"=>$f,
                "documentosrelacionados"=>$documentosrelacionados,
                "datageneral" => $datageneral,
                "cliente" => $cliente,
                "regimenfiscalcliente" => $regimenfiscalcliente,
                "est" => $est,
                "NombreAgente" => $NombreAgente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "pagare"=> $pagare,
                "tipodetalles" => $tipodetalles,
                "fechavence" => Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString(),
                "subtotalfactura"=>Helpers::convertirvalorcorrecto($f->SubTotal),
                "ivafactura"=>Helpers::convertirvalorcorrecto($f->Iva),
                "totalfactura"=>Helpers::convertirvalorcorrecto($f->Total),
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($f->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.facturas.formato_pdf_facturas', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        //obtener XML
        if($request->incluir_xml == 1){
            $factura = Factura::where('Factura', $request->emaildocumento)->first();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
            $nombre_xml = "FacturaNo".$factura->Factura.'##'.$factura->UUID.'.xml';
            Storage::disk('local')->put($nombre_xml, $descargar_xml);
            $url_xml = Storage::disk('local')->getAdapter()->applyPathPrefix($nombre_xml);
        }else{
            $url_xml = "";
        }
        try{
            //enviar correo electrónico
            $datosfactura = Factura::where('Factura', $request->emaildocumento)->first();
            $datoscliente = Cliente::where('Numero', $datosfactura->Cliente)->first();
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
            if (file_exists($url_xml) != false) {
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
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
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml);
                    });
                }
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
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
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf");
                    });
                }
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
    //generacion de formato en PDF INTERNO
    public function facturas_generar_pdfs_cliente_indiv($documento){
        $facturas = Factura::where('Factura', $documento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($facturas as $f){
            $facturadetalles = FacturaDetalle::where('Factura', $f->Factura)->get();
            $arraytipofactura=array();
            $tipodetalles = '';
            foreach($facturadetalles as $fd){
                if($fd->Facturar == 'PRODUCTOS'){
                    array_push($arraytipofactura, $fd->Remision);
                    $tipodetalles = 'remisiones';
                }else if($fd->Facturar == 'SERVICIO'){
                    array_push($arraytipofactura, $fd->Orden);
                    $tipodetalles = 'ordenes';
                }else if($fd->Facturar == 'LIBRE'){
                    $tipodetalles = 'libre';
                }
            }
            //if la factura es para remisiones o ordenes
            if(sizeof($arraytipofactura) > 0){
                $serviciosoremisionesfactura = array_unique($arraytipofactura);
                sort($serviciosoremisionesfactura, SORT_NATURAL | SORT_FLAG_CASE);
                $datageneral = array();
                foreach($serviciosoremisionesfactura as $sorf){
                    $datadetalle=array();
                    switch ($tipodetalles) {
                        case 'remisiones':
                            $datosgenerales = Remision::where('Remision', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observacionesdocumento = $datosgenerales->Obs;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Remision', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $insumodet = RemisionDetalle::where('Remision', $dfr->Remision)->where('Codigo', $dfr->Codigo)->first();
                                if($insumodet == null){
                                    $insumodetalle = "";
                                }else{
                                    $insumodetalle = $insumodet->Insumo;
                                }
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "insumodetalle"=>$insumodetalle,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                        case 'ordenes':
                            $datosgenerales = OrdenTrabajo::where('Orden', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observaciones = $datosgenerales->ObsOrden;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Orden', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                    }
                    $datageneral[]=array(
                        "datosgenerales"=>$datosgenerales,
                        "datadetalle" => $datadetalle,
                        "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                        "obsdocumento" => $observacionesdocumento
                    );
                }
            //si la factura es libre
            }else{
                $datageneral = array();
                $datadetalle=array();
                $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->get();
                $sumatotaldetalles = 0;
                foreach($detallesfacturaremision as $dfr){
                    $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                    $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                    $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                    $datadetalle[]=array(
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                        "codigodetalle"=>$dfr->Codigo,
                        "descripciondetalle"=>$dfr->Descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                        "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                        "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                        "claveproducto" => $claveproducto,
                        "claveunidad" => $claveunidad
                    );
                }
                $datageneral[]=array(
                    "datadetalle" => $datadetalle,
                    "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                    "obsdocumento" => ""
                );
            }
            $cliente = Cliente::where('Numero', $f->Cliente)->first();
            $estadoclienteexiste = Estado::where('Clave', $cliente->Estado)->count();
            if($estadoclienteexiste > 0){
                $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
                $est = $estadocliente->Nombre;
            }else{
                $est = $cliente->Estado;
            }
            //$regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cliente->RegimenFiscal)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $f->RegimenFiscalReceptor)->first();
            $contaragente = Agente::where('Numero', $f->Agente)->count();
            $NombreAgente = "";
            if($contaragente > 0){
                $agente = Agente::where('Numero', $f->Agente)->first();
                $NombreAgente = $agente->Nombre;
            }
            $formapago = FormaPago::where('Clave', $f->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $f->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $f->UsoCfdi)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $f->RegimenFiscal)->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($f->Total, 2, 'M.N.');
            $documentosrelacionados = FacturaDocumento::where('Factura', $f->Factura)->first();
            //dd($documentosrelacionados);
            $foliofiscalfactura = FolioComprobanteFactura::where('Serie', $f->Serie)->where('Esquema', $f->Esquema)->first();
            $pagares = $foliofiscalfactura->Pagare;
            $reemplazarbeneficiario = str_replace("%beneficiario", $this->empresa->Empresa, $pagares);
            $reemplazarvence = str_replace("%vence", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()), $reemplazarbeneficiario);
            $reemplazartotal = str_replace("%total", Helpers::convertirvalorcorrecto($f->Total), $reemplazarvence);
            $reemplazartotalletra = str_replace("%letratotal", $totalletras, $reemplazartotal);
            $reemplazarortorgante = str_replace("%nombre", $cliente->Nombre.' ('.$cliente->Numero.')', $reemplazartotalletra);
            $reemplazardomicilio = str_replace("%direccion", $cliente->Calle.' '.$cliente->noExterior.' '.$cliente->noInterior.' Colonia: '.$cliente->Colonia.' Estado: '.$cliente->Localidad, $reemplazarortorgante);
            $reemplazarciudad = str_replace("%ciudad", $cliente->Municipio.' C.P. '.$cliente->CodigoPostal, $reemplazardomicilio);
            $reemplazarestado = str_replace("%estadobeneficiario", $this->empresa->Estado, $reemplazarciudad);
            $reemplazarfecha = str_replace("%fecha", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->toDateTimeString()), $reemplazarestado);
            $reemplazarbr = str_replace("%br", "\n\n", $reemplazarfecha);
            $pagare = $reemplazarbr;
            $data[]=array(
                "factura"=>$f,
                "documentosrelacionados"=>$documentosrelacionados,
                "datageneral" => $datageneral,
                "cliente" => $cliente,
                "regimenfiscalcliente"=> $regimenfiscalcliente,
                "est" => $est,
                "NombreAgente" => $NombreAgente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "pagare"=> $pagare,
                "tipodetalles" => $tipodetalles,
                "fechavence" => Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString(),
                "subtotalfactura"=>Helpers::convertirvalorcorrecto($f->SubTotal),
                "ivafactura"=>Helpers::convertirvalorcorrecto($f->Iva),
                "totalfactura"=>Helpers::convertirvalorcorrecto($f->Total),
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($f->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.facturas.formato_pdf_clientes_facturas', compact('data'))
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
    public function facturas_enviar_pdfs_clientes_email(Request $request){
        $facturas = Factura::where('Factura', $request->emaildocumento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($facturas as $f){
            $facturadetalles = FacturaDetalle::where('Factura', $f->Factura)->get();
            $arraytipofactura=array();
            $tipodetalles = '';
            foreach($facturadetalles as $fd){
                if($fd->Facturar == 'PRODUCTOS'){
                    array_push($arraytipofactura, $fd->Remision);
                    $tipodetalles = 'remisiones';
                }else if($fd->Facturar == 'SERVICIO'){
                    array_push($arraytipofactura, $fd->Orden);
                    $tipodetalles = 'ordenes';
                }else if($fd->Facturar == 'LIBRE'){
                    $tipodetalles = 'libre';
                }
            }
            //if la factura es para remisiones o ordenes
            if(sizeof($arraytipofactura) > 0){
                $serviciosoremisionesfactura = array_unique($arraytipofactura);
                sort($serviciosoremisionesfactura, SORT_NATURAL | SORT_FLAG_CASE);
                $datageneral = array();
                foreach($serviciosoremisionesfactura as $sorf){
                    $datadetalle=array();
                    switch ($tipodetalles) {
                        case 'remisiones':
                            $datosgenerales = Remision::where('Remision', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observacionesdocumento = $datosgenerales->Obs;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Remision', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $insumodet = RemisionDetalle::where('Remision', $dfr->Remision)->where('Codigo', $dfr->Codigo)->first();
                                if($insumodet == null){
                                    $insumodetalle = "";
                                }else{
                                    $insumodetalle = $insumodet->Insumo;
                                }
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "insumodetalle"=>$insumodetalle,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                        case 'ordenes':
                            $datosgenerales = OrdenTrabajo::where('Orden', $sorf)->first();
                            $observacionesdocumento = '';
                            if($datosgenerales != null){
                                $observaciones = $datosgenerales->ObsOrden;
                            }
                            $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->where('Orden', $sorf)->get();
                            $sumatotaldetalles = 0;
                            foreach($detallesfacturaremision as $dfr){
                                $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                                $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                                $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                                $producto = Producto::where('Codigo', $dfr->Codigo)->first();
                                $datadetalle[]=array(
                                    "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                                    "codigodetalle"=>$dfr->Codigo,
                                    "descripciondetalle"=>$dfr->Descripcion,
                                    "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                                    "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                                    "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                                    "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                                    "claveproducto" => $claveproducto,
                                    "claveunidad" => $claveunidad
                                );
                            }
                            break;
                    }
                    $datageneral[]=array(
                        "datosgenerales"=>$datosgenerales,
                        "datadetalle" => $datadetalle,
                        "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                        "obsdocumento" => $observacionesdocumento
                    );
                }
            //si la factura es libre
            }else{
                $datageneral = array();
                $datadetalle=array();
                $detallesfacturaremision = FacturaDetalle::where('Factura', $f->Factura)->get();
                $sumatotaldetalles = 0;
                foreach($detallesfacturaremision as $dfr){
                    $sumatotaldetalles = $sumatotaldetalles + $dfr->SubTotal;
                    $claveproducto = ClaveProdServ::where('Clave', $dfr->ClaveProducto)->first();
                    $claveunidad = ClaveUnidad::where('Clave', $dfr->ClaveUnidad)->first();
                    $datadetalle[]=array(
                        "cantidaddetalle"=> Helpers::convertirvalorcorrecto($dfr->Cantidad),
                        "codigodetalle"=>$dfr->Codigo,
                        "descripciondetalle"=>$dfr->Descripcion,
                        "preciodetalle" => Helpers::convertirvalorcorrecto($dfr->Precio),
                        "subtotaldetalle" => Helpers::convertirvalorcorrecto($dfr->SubTotal),
                        "impuestodetalle" => Helpers::convertirvalorcorrecto($dfr->Impuesto),
                        "ivadetalle" => Helpers::convertirvalorcorrecto($dfr->Iva),
                        "claveproducto" => $claveproducto,
                        "claveunidad" => $claveunidad
                    );
                }
                $datageneral[]=array(
                    "datadetalle" => $datadetalle,
                    "sumatotaldetalles" => Helpers::convertirvalorcorrecto($sumatotaldetalles),
                    "obsdocumento" => ""
                );
            }
            $cliente = Cliente::where('Numero', $f->Cliente)->first();
            $estadoclienteexiste = Estado::where('Clave', $cliente->Estado)->count();
            if($estadoclienteexiste > 0){
                $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
                $est = $estadocliente->Nombre;
            }else{
                $est = $cliente->Estado;
            }
            //$regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cliente->RegimenFiscal)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $f->RegimenFiscalReceptor)->first();
            $contaragente = Agente::where('Numero', $f->Agente)->count();
            $NombreAgente = "";
            if($contaragente > 0){
                $agente = Agente::where('Numero', $f->Agente)->first();
                $NombreAgente = $agente->Nombre;
            }
            $formapago = FormaPago::where('Clave', $f->FormaPago)->first();
            $metodopago = MetodoPago::where('Clave', $f->MetodoPago)->first();
            $usocfdi = UsoCFDI::where('Clave', $f->UsoCfdi)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $f->Folio . '')->where('Serie', '' . $f->Serie . '')->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $f->RegimenFiscal)->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($f->Total, 2, 'M.N.');
            $documentosrelacionados = FacturaDocumento::where('Factura', $f->Factura)->first();
            //dd($documentosrelacionados);
            $foliofiscalfactura = FolioComprobanteFactura::where('Serie', $f->Serie)->where('Esquema', $f->Esquema)->first();
            $pagares = $foliofiscalfactura->Pagare;
            $reemplazarbeneficiario = str_replace("%beneficiario", $this->empresa->Empresa, $pagares);
            $reemplazarvence = str_replace("%vence", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()), $reemplazarbeneficiario);
            $reemplazartotal = str_replace("%total", Helpers::convertirvalorcorrecto($f->Total), $reemplazarvence);
            $reemplazartotalletra = str_replace("%letratotal", $totalletras, $reemplazartotal);
            $reemplazarortorgante = str_replace("%nombre", $cliente->Nombre.' ('.$cliente->Numero.')', $reemplazartotalletra);
            $reemplazardomicilio = str_replace("%direccion", $cliente->Calle.' '.$cliente->noExterior.' '.$cliente->noInterior.' Colonia: '.$cliente->Colonia.' Estado: '.$cliente->Localidad, $reemplazarortorgante);
            $reemplazarciudad = str_replace("%ciudad", $cliente->Municipio.' C.P. '.$cliente->CodigoPostal, $reemplazardomicilio);
            $reemplazarestado = str_replace("%estadobeneficiario", $this->empresa->Estado, $reemplazarciudad);
            $reemplazarfecha = str_replace("%fecha", Helpers::fecha_espanol(Carbon::parse($f->Fecha)->toDateTimeString()), $reemplazarestado);
            $reemplazarbr = str_replace("%br", "\n\n", $reemplazarfecha);
            $pagare = $reemplazarbr;
            $data[]=array(
                "factura"=>$f,
                "documentosrelacionados"=>$documentosrelacionados,
                "datageneral" => $datageneral,
                "cliente" => $cliente,
                "regimenfiscalcliente"=>$regimenfiscalcliente,
                "est" => $est,
                "NombreAgente" => $NombreAgente,
                "formapago" => $formapago,
                "metodopago" => $metodopago,
                "usocfdi" => $usocfdi,
                "comprobante" => $comprobante,
                "comprobantetimbrado" => $comprobantetimbrado,
                "regimenfiscal"=> $regimenfiscal,
                "pagare"=> $pagare,
                "tipodetalles" => $tipodetalles,
                "fechavence" => Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString(),
                "subtotalfactura"=>Helpers::convertirvalorcorrecto($f->SubTotal),
                "ivafactura"=>Helpers::convertirvalorcorrecto($f->Iva),
                "totalfactura"=>Helpers::convertirvalorcorrecto($f->Total),
                "tipocambiofactura"=>Helpers::convertirvalorcorrecto($f->TipoCambio),
                "totalletras"=>$totalletras,
                "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.facturas.formato_pdf_clientes_facturas', compact('data'))
        ->setPaper('Letter')
        //->setOption('footer-left', 'E.R. '.Auth::user()->user.'')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        //obtener XML
        if($request->incluir_xml == 1){
            $factura = Factura::where('Factura', $request->emaildocumento)->first();
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
            $nombre_xml = "FacturaNo".$factura->Factura.'##'.$factura->UUID.'.xml';
            Storage::disk('local')->put($nombre_xml, $descargar_xml);
            $url_xml = Storage::disk('local')->getAdapter()->applyPathPrefix($nombre_xml);
        }else{
            $url_xml = "";
        }
        try{
            //enviar correo electrónico
            $datosfactura = Factura::where('Factura', $request->emaildocumento)->first();
            $datoscliente = Cliente::where('Numero', $datosfactura->Cliente)->first();
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
            if (file_exists($url_xml) != false) {
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
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
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml)
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento, $url_xml) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($url_xml);
                    });
                }
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                if($request->archivoadjunto != null && $request->archivoadjunto2 != null) {
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $urlarchivoadjunto2, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
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
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $arraycc, $urlarchivoadjunto, $correos, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto)) {
                        unlink($urlarchivoadjunto);
                    }
                }else if($request->archivoadjunto == null && $request->archivoadjunto2 != null){
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $urlarchivoadjunto2, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf")
                                ->attach($urlarchivoadjunto2);
                    });
                    //eliminar xml de storage/xml_cargados
                    if (file_exists($urlarchivoadjunto2)) {
                        unlink($urlarchivoadjunto2);
                    }
                }else{
                    Mail::send('correos.facturacion.enviofacturas', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol','datosfactura', 'datoscliente'), function($message) use ($nombre, $receptor, $correos, $arraycc, $asunto, $pdf, $emaildocumento) {
                        $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                                ->cc($arraycc)
                                ->subject($asunto)
                                ->attachData($pdf->output(), "FacturaNo".$emaildocumento.".pdf");
                    });
                }
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
    public function facturas_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Facturas', Auth::user()->id);
        return Excel::download(new FacturasExport($configuraciones_tabla['campos_consulta'],$request->periodo), "facturas-".$request->periodo.".xlsx");
    }
    //configuracion tabla
    public function facturas_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('Facturas', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'Facturas')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='Facturas';
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
        return redirect()->route('facturas');
    }
    //verificar si se puede timbrar la factura
    public function facturas_verificar_si_continua_timbrado(Request $request){
        $Factura = Factura::where('factura', $request->factura)->first();
        $data = array(
            'Esquema' => $Factura->Esquema,
            'Status' => $Factura->Status,
            'UUID' => $Factura->UUID
        );
        return response()->json($data);
    }
    //timbrar factura
    public function facturas_timbrar_factura(Request $request){
        $factura = Factura::where('Factura', $request->facturatimbrado)->first();
        $detallesfactura = FacturaDetalle::where('Factura', $request->facturatimbrado)->orderBy('Item','ASC')->get();
        $detallesdocumentosfactura = FacturaDocumento::where('Factura', $request->facturatimbrado)->get();
        $cliente = Cliente::where('Numero', $factura->Cliente)->first();
        $arraytest = array();
        foreach($detallesfactura as $df){
            if($df->Impuesto == 0.000000){
                array_push($arraytest,  array(
                                            "quantity" => Helpers::convertirvalorcorrecto($df->Cantidad),
                                            "discount" => Helpers::convertirvalorcorrecto($df->Descuento),
                                            "product" =>
                                                array(
                                                    "description" => $df->Descripcion,
                                                    "product_key" => $df->ClaveProducto,
                                                    "unit_key" => $df->ClaveUnidad,
                                                    "price" => Helpers::convertirvalorcorrecto($df->Precio),
                                                    "tax_included" => false,
                                                    "taxability" => "01",
                                                    "taxes" => [],
                                                    "sku" => $df->Codigo
                                                )
                                        )
                );
            }else{
                array_push($arraytest,  array(
                                            "quantity" => Helpers::convertirvalorcorrecto($df->Cantidad),
                                            "discount" => Helpers::convertirvalorcorrecto($df->Descuento),
                                            "product" =>
                                                array(
                                                    "description" => $df->Descripcion,
                                                    "product_key" => $df->ClaveProducto,
                                                    "unit_key" => $df->ClaveUnidad,
                                                    "price" => Helpers::convertirvalorcorrecto($df->Precio),
                                                    "tax_included" => false,
                                                    "sku" => $df->Codigo
                                                )
                                        )
                );
            }
        }
        if($cliente->Rfc == 'XAXX010101000'){
            //asignar periodicidad en ingles para facturas globales
            switch($factura->Periodicidad){
                case "01":
                    $periodicidad = "day";
                    break;
                case "02":
                    $periodicidad = "week";
                    break;
                case "03":
                    $periodicidad = "fortnight";
                    break;
                case "04":
                    $periodicidad = "month";
                    break;
                case "05":
                    $periodicidad = "two_months";
                    break;
            }
            //obtener el año actual
            $fechaactual = Carbon::now();
            $global = array(
                "periodicity" => $periodicidad,
                "months" => $factura->Meses,
                "year" => $fechaactual->year
            );
            if($factura->TipoRelacion != ""){
                $arraydoc = array();
                foreach($detallesdocumentosfactura as $ddf){
                    array_push($arraydoc, $ddf->UUID);
                }
                //FACTURA
                // Crea una nueva factura
                $invoice = array(
                    "customer" => array(
                        "legal_name" => $cliente->Nombre,
                        "tax_id" => $cliente->Rfc,

                        //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                        "tax_system" => $cliente->RegimenFiscal,
                        "address" =>
                            array(
                                "zip" => $cliente->CodigoPostal,
                            )
                        //fin cfdi 4.0

                    ),
                    "items" => $arraytest,
                    "payment_form" => $factura->FormaPago,
                    "payment_method" => $factura->MetodoPago,

                    //se debe cambiar la forma de relacion los documentos y en lugar de mandar arra products, se manda array items como en las facturas de ingreso con facturapi 2.0
                    "related_documents" => array(
                        array(
                            "relationship" => $factura->TipoRelacion,
                            "documents" => $arraydoc
                        )
                    ),
                    /*
                    //datos para facturar en facturapi 1.0
                    "relation" => $factura->TipoRelacion,
                    "related" => $arraydoc,
                    */
                    "global" => $global,
                    "folio_number" => $factura->Folio,
                    "series" => $factura->Serie,
                    "currency" => $factura->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($factura->TipoCambio),
                    "conditions" => $factura->CondicionesDePago
                );
            }else{
                //FACTURA
                // Crea una nueva factura
                $invoice = array(
                    "customer" => array(
                        "legal_name" => $cliente->Nombre,
                        "tax_id" => $cliente->Rfc,

                        //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                        "tax_system" => $cliente->RegimenFiscal,
                        "address" =>
                            array(
                                "zip" => $cliente->CodigoPostal,
                            )
                        //fin cfdi 4.0

                    ),
                    "items" => $arraytest,
                    "payment_form" => $factura->FormaPago,
                    "payment_method" => $factura->MetodoPago,
                    "global" => $global,
                    "folio_number" => $factura->Folio,
                    "series" => $factura->Serie,
                    "currency" => $factura->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($factura->TipoCambio),
                    "conditions" => $factura->CondicionesDePago
                );
            }
        }else{
            if($factura->TipoRelacion != ""){
                $arraydoc = array();
                foreach($detallesdocumentosfactura as $ddf){
                    array_push($arraydoc, $ddf->UUID);
                }
                //FACTURA
                // Crea una nueva factura
                $invoice = array(
                    "customer" => array(
                        "legal_name" => $cliente->Nombre,
                        "tax_id" => $cliente->Rfc,

                        //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                        "tax_system" => $cliente->RegimenFiscal,
                        "address" =>
                            array(
                                "zip" => $cliente->CodigoPostal,
                            )
                        //fin cfdi 4.0

                    ),
                    "items" => $arraytest,
                    "payment_form" => $factura->FormaPago,
                    "payment_method" => $factura->MetodoPago,

                    //se debe cambiar la forma de relacion los documentos y en lugar de mandar arra products, se manda array items como en las facturas de ingreso con facturapi 2.0
                    "related_documents" => array(
                        array(
                            "relationship" => $factura->TipoRelacion,
                            "documents" => $arraydoc
                        )
                    ),
                    /*
                    //datos para facturar en facturapi 1.0
                    "relation" => $factura->TipoRelacion,
                    "related" => $arraydoc,
                    */
                    "folio_number" => $factura->Folio,
                    "series" => $factura->Serie,
                    "currency" => $factura->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($factura->TipoCambio),
                    "conditions" => $factura->CondicionesDePago
                );
            }else{
                //FACTURA
                // Crea una nueva factura
                $invoice = array(
                    "customer" => array(
                        "legal_name" => $cliente->Nombre,
                        "tax_id" => $cliente->Rfc,

                        //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                        "tax_system" => $cliente->RegimenFiscal,
                        "address" =>
                            array(
                                "zip" => $cliente->CodigoPostal,
                            )
                        //fin cfdi 4.0

                    ),
                    "items" => $arraytest,
                    "payment_form" => $factura->FormaPago,
                    "payment_method" => $factura->MetodoPago,
                    "folio_number" => $factura->Folio,
                    "series" => $factura->Serie,
                    "currency" => $factura->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($factura->TipoCambio),
                    "conditions" => $factura->CondicionesDePago
                );
            }
        }
        $new_invoice = $this->facturapi->Invoices->create( $invoice );
        $result = json_encode($new_invoice);
        $result2 = json_decode($result, true);
        if(array_key_exists('ok', $result2) == true){
            $mensaje = $new_invoice->message;
            $tipomensaje = "error";
            $data = array(
                        'mensaje' => "Error, ".$mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }else{
            //obtener datos del xml del documento timbrado para guardarlo en la tabla comprobantes
            $descargar_xml = $this->facturapi->Invoices->download_xml($new_invoice->id); // stream containing the XML file or
            $xml = simplexml_load_string($descargar_xml);
            $comprobante = $xml->attributes();
            $CertificadoCFD = $comprobante['NoCertificado'];
            //obtener datos generales del xml nodo Emisor
            $activar_namespaces = $xml->getNameSpaces(true);
            $namespaces = $xml->children($activar_namespaces['cfdi']);
            //obtener UUID del xml timbrado digital
            $activar_namespaces = $namespaces->Complemento->getNameSpaces(true);
            $namespaces_uuid = $namespaces->Complemento->children($activar_namespaces['tfd']);
            $atributos_complemento = $namespaces_uuid->TimbreFiscalDigital->attributes();
            $NoCertificadoSAT = $atributos_complemento['NoCertificadoSAT'];
            $SelloCFD = $atributos_complemento['SelloCFD'];
            $SelloSAT = $atributos_complemento['SelloSAT'];
            $fechatimbrado = $atributos_complemento['FechaTimbrado'];
            $cadenaoriginal = "||".$atributos_complemento['Version']."|".$new_invoice->uuid."|".$atributos_complemento['FechaTimbrado']."|".$atributos_complemento['SelloCFD']."|".$atributos_complemento['NoCertificadoSAT']."||";
            //guardar en tabla comprobante
            $Comprobante = new Comprobante;
            $Comprobante->Comprobante = 'Factura';
            $Comprobante->Tipo = $new_invoice->type;
            //version 4.0
            $Comprobante->Version = '4.0';
            //version 3.3
            //$Comprobante->Version = '3.3';
            $Comprobante->Serie = $new_invoice->series;
            $Comprobante->Folio = $new_invoice->folio_number;
            $Comprobante->UUID = $new_invoice->uuid;
            $Comprobante->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $Comprobante->SubTotal = $factura->SubTotal;
            $Comprobante->Descuento = $factura->Descuento;
            $Comprobante->Total = $factura->Total;
            $Comprobante->EmisorRfc = $factura->EmisorRfc;
            $Comprobante->ReceptorRfc = $factura->ReceptorRfc;
            $Comprobante->FormaPago = $new_invoice->payment_form;
            $Comprobante->MetodoPago = $new_invoice->payment_method;
            $Comprobante->UsoCfdi = $new_invoice->use;
            $Comprobante->Moneda = $new_invoice->currency;
            $Comprobante->TipoCambio = Helpers::convertirvalorcorrecto($new_invoice->exchange);
            $Comprobante->CertificadoSAT = $NoCertificadoSAT;
            $Comprobante->CertificadoCFD = $CertificadoCFD;
            $Comprobante->FechaTimbrado = $fechatimbrado;
            $Comprobante->CadenaOriginal = $cadenaoriginal;
            $Comprobante->selloSAT = $SelloSAT;
            $Comprobante->selloCFD = $SelloCFD;
            //$Comprobante->CfdiTimbrado = $new_invoice->type;
            $Comprobante->Periodo = $this->periodohoy;
            $Comprobante->IdFacturapi = $new_invoice->id;
            $Comprobante->UrlVerificarCfdi = $new_invoice->verification_url;
            $Comprobante->save();
            //Colocar UUID en factura
            Factura::where('Factura', $request->facturatimbrado)
                            ->update([
                                'FechaTimbrado' => $fechatimbrado,
                                'UUID' => $new_invoice->uuid
                            ]);
            // Enviar a más de un correo (máx 10)
            $this->facturapi->Invoices->send_by_email(
                $new_invoice->id,
                array(
                    "osbaldo.anzaldo@utpcamiones.com.mx",
                    //"marco.baltazar@utpcamiones.com.mx",
                )
            );
            $mensaje = "Correcto, el documento se timbro correctamente";
            $tipomensaje = "success";
            $data = array(
                        'mensaje' => $mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }
    }
    //verificar cancelacion timbre
    public function facturas_verificar_si_continua_baja_timbre(Request $request){
        $obtener_factura = '';
        $comprobante = '';
        $factura = Factura::where('Factura', $request->facturabajatimbre)->first();
        $existe_comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->count();
        if($existe_comprobante > 0){
            $comprobante = Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
            $obtener_factura = $this->facturapi->Invoices->retrieve($comprobante->IdFacturapi); // obtener factura
        }
        $data = array(
            'obtener_factura' => $obtener_factura,
            'factura' => $factura,
            'comprobante' => $comprobante
        );
        return response()->json($data);
    }
    //cancelar timbre
    public function facturas_baja_timbre(Request $request){
        //cancelar timbre facturapi
        //con version 1.0 facturapi sin motivo de baja
        //$timbrecancelado = $this->facturapi->Invoices->cancel($request->iddocumentofacturapi);
        //con version 2.0 facturapi con motivo de baja
        $timbrecancelado = $this->facturapi->Invoices->cancel(
            $request->iddocumentofacturapi,
            [
              "motive" => $request->motivobajatimbre
            ]
        );
        $result = json_encode($timbrecancelado);
        $result2 = json_decode($result, true);
        if(array_key_exists('ok', $result2) == true){
            $mensaje = $timbrecancelado->message;
            $tipomensaje = "error";
            $data = array(
                        'mensaje' => "Error, ".$mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }else{
            //colocar fecha de cancelacion en tabla comprobante
            $factura = Factura::where('Factura', $request->facturabajatimbre)->first();
            Comprobante::where('Comprobante', 'Factura')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')
            ->update([
                'FechaCancelacion' => Helpers::fecha_exacta_accion_datetimestring()
            ]);
            $mensaje = "Correcto, se cancelo el timbre correctamente";
            $tipomensaje = "success";
            $data = array(
                        'mensaje' => $mensaje,
                        'tipomensaje' => $tipomensaje
                    );
            return response()->json($data);
        }
    }
}
