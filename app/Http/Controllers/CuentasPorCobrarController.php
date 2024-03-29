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
use App\Exports\CuentasPorCobrarExport;
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\Banco;
use App\Cliente;
use App\Factura;
use App\FacturaDetalle;
use App\Pais;
use App\Estado;
use App\Municipio;
use App\CodigoPostal;
use App\FormaPago;
use App\UsoCFDI;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\NotaClienteDocumento;
use App\BitacoraDocumento;
use Luecano\NumeroALetras\NumeroALetras;
use App\Configuracion_Tabla;
use App\VistaCuentaPorCobrar;
use App\MetodoPago;
use App\c_RegimenFiscal;
use App\c_TipoRelacion;
use App\FolioComprobantePago;
use App\Comprobante;
use App\User_Rel_Serie;
use App\c_ObjetoImp;
use App\c_Exportacion;
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;
use ZipArchive;
use File;

class CuentasPorCobrarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //API FACTURAPI
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //
    }

    public function cuentas_por_cobrar(){
        $contarseriesasignadasausuario = User_Rel_Serie::where('user_id', Auth::user()->id)->where('documento_serie', 'PAGOS')->count();
        if($contarseriesasignadasausuario > 0){
            $contarserieusuario = DB::table('user_rel_series as urs')
            ->join('Folios Comprobantes Pagos as fcf', 'urs.serie_id', '=', 'fcf.Numero')
            ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Predeterminar')
            ->where('fcf.Predeterminar', '+')
            ->where('urs.user_id', Auth::user()->id)
            ->where('urs.documento_serie', 'PAGOS')
            ->count();
            if($contarserieusuario == 0){
                $FolioComprobanteFactura = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Pagos as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Predeterminar')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'PAGOS')
                ->orderBy('fcf.Numero', 'DESC')
                ->take(1)->get();
                $serieusuario = $FolioComprobanteFactura[0]->Serie;
                $esquema = $FolioComprobanteFactura[0]->Esquema;
            }else{
                $FolioComprobanteFactura = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Pagos as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Predeterminar')
                ->where('fcf.Predeterminar', '+')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'PAGOS')
                ->first();
                $serieusuario = $FolioComprobanteFactura->Serie;
                $esquema = $FolioComprobanteFactura->Esquema;
            }
        }else{
            $contarserieusuario = FolioComprobantePago::where('Predeterminar', '+')->count();
            if($contarserieusuario == 0){
                $FolioComprobantePago = FolioComprobantePago::orderBy('Numero','DESC')->take(1)->get();
                $serieusuario = $FolioComprobantePago[0]->Serie;
                $esquema = $FolioComprobantePago[0]->Esquema;
            }else{
                $FolioComprobantePago = FolioComprobantePago::where('Predeterminar', '+')->first();
                $serieusuario = $FolioComprobantePago->Serie;
                $esquema = $FolioComprobantePago->Esquema;
            }
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CuentasPorCobrar', Auth::user()->id);
        $configuracion_tabla = $configuraciones_tabla['configuracion_tabla'];
        $rutaconfiguraciontabla = route('cuentas_por_cobrar_guardar_configuracion_tabla');
        $urlgenerarformatoexcel = route('cuentas_por_cobrar_exportar_excel');
        $rutacreardocumento = route('cuentas_por_cobrar_generar_pdfs');
        $lugarexpedicion = $this->lugarexpedicion;
        $claveregimenfiscal = '';
        $regimenfiscal = '';
        if($this->regimenfiscal != ''){
            $c_RegimenFiscal = c_RegimenFiscal::where('Clave', $this->regimenfiscal)->first();
            $claveregimenfiscal = $c_RegimenFiscal->Clave;
            $regimenfiscal = $c_RegimenFiscal->Nombre;
        }
        return view('registros.cuentasporcobrar.cuentasporcobrar', compact('serieusuario','esquema','configuracion_tabla','rutaconfiguraciontabla','urlgenerarformatoexcel','rutacreardocumento','lugarexpedicion','claveregimenfiscal','regimenfiscal'));
    }

    //obtener registro tabla
    public function cuentas_por_cobrar_obtener(Request $request){
        if($request->ajax()){
            $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CuentasPorCobrar', Auth::user()->id);
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            $data = VistaCuentaPorCobrar::select($configuraciones_tabla['campos_consulta'])->where('Periodo', $periodo);
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
                ->withQuery('sumaabono', function($data) {
                    return $data->sum('Abono');
                })
                ->addColumn('operaciones', function($data){
                    $operaciones = '<div class="dropdown">'.
                                        '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                            'OPERACIONES <span class="caret"></span>'.
                                        '</button>'.
                                        '<ul class="dropdown-menu">'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Pago .'\')">Cambios</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="desactivar(\''.$data->Pago .'\')">Bajas</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="'.route('cuentas_por_cobrar_generar_pdfs_indiv',['documento'=>$data->Pago,'tipodocumento'=>0]).'" target="_blank">Ver Documento Normal PDF</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="'.route('cuentas_por_cobrar_generar_pdfs_indiv',['documento'=>$data->Pago,'tipodocumento'=>1]).'" target="_blank">Ver Documento Poliza de Ingreso PDF</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Pago .'\')">Enviar Documento Normal por Correo</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Pago.'\',1)">Enviar Documento Poliza de Ingreso por Correo</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="timbrarpago(\''.$data->Pago .'\')">Timbrar Pago</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="cancelartimbre(\''.$data->Pago .'\')">Cancelar Timbre</a></li>'.
                                            '<li><a class="paddingmenuopciones" href="javascript:void(0);" onclick="generardocumentoeniframe(\''.$data->Pago .'\')">Imprimir Documento PDF</a></li>'.
                                        '</ul>'.
                                    '</div>';
                    return $operaciones;
                })
                ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                //->addColumn('Abono', function($data){ return $data->Abono; })
                ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
                ->addColumn('Facturas', function($data){ return substr($data->Facturas, 0, 70); })
                ->rawColumns(['operaciones'])
                ->make(true);
        }
    }

    //obtener ultimo folio
    public function cuentas_por_cobrar_obtener_ultimo_folio(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXCobrar', $request->serie);
        return response()->json($folio);
    }

    //obtener datetime local
    public function cuentas_por_cobrar_obtener_fecha_datetime(){
        $fecha = Helpers::fecha_exacta_accion_datetimelocal();
        return response()->json($fecha);
    }

    //obtener clientes
    public function cuentas_por_cobrar_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = DB::table('Clientes as c')
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->leftJoin('c_RegimenFiscal as rf', 'rf.Clave', '=', 'c.RegimenFiscal')
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre as NombreUsoCfdi', 'p.Clave as ClavePais', 'p.Nombre as NombrePais', 'rf.Clave as ClaveRegimenFiscal', 'rf.Nombre as RegimenFiscal')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\',\''.$data->ClaveRegimenFiscal.'\',\''.$data->RegimenFiscal.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener cliente por numero
    public function cuentas_por_cobrar_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $rfc = '';
        $claveformapago = '';
        $formapago = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $datos = DB::table('Clientes as c')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->leftJoin('c_RegimenFiscal as rf', 'rf.Clave', '=', 'c.RegimenFiscal')
            ->select('c.Numero', 'c.Status', 'mp.Clave AS clavemetodopago', 'mp.Nombre AS metodopago', 'uc.Clave AS claveusocfdi', 'uc.Nombre AS usocfdi', 'p.Clave AS claveresidenciafiscal', 'p.Nombre AS residenciafiscal', 'rf.Clave as ClaveRegimenFiscal', 'rf.Nombre as RegimenFiscal')
            ->where('c.Numero', $request->numerocliente)
            ->where('c.Status', 'ALTA')
            ->get();
            $claveformapago = '03';
            $formapago = 'Transferencia electrónica de fondos';
            $claveregimenfiscalreceptor = $datos[0]->ClaveRegimenFiscal;
            $regimenfiscalreceptor = $datos[0]->RegimenFiscal;
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $facturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->orderBy('Folio', 'DESC')->get();
            $numerofacturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->count();
            $filasfacturas= '';
            $contadorfilas = 0;
            if($numerofacturas > 0){
                $objetosimp = c_ObjetoImp::all();
                $selectobjetosimp = "<option selected disabled hidden>Selecciona</option>";
                foreach($objetosimp as $oi){
                    if($oi->Numero == 2){
                        $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.' selected>'.$oi->Descripcion.'</option>';
                    }else{
                        $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.'>'.$oi->Descripcion.'</option>';
                    }
                }
                foreach($facturas as $f){
                    $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($f->Iva, $f->SubTotal);
                    $tipooperacion = $request->tipooperacion;
                    $numparcialidades = CuentaXCobrarDetalle::where('Factura', $f->Factura)->where('Abono', '>', 0)->count();
                    $numeroparcialidades = 1;
                    if($numparcialidades > 0){
                        $numeroparcialidades = $numeroparcialidades + $numparcialidades;
                    }
                    //detalles factura
                    $filasfacturas= $filasfacturas.
                    '<tr class="filasfacturas" id="filafactura'.$contadorfilas.'">'.
                        '<td class="tdmod"><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$f->Factura.'" readonly>'.$f->Factura.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($f->Fecha).'" readonly>'.Helpers::fecha_espanol($f->Fecha).'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$f->Plazo.'" readonly>'.$f->Plazo.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()).'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');calculartotal('.$contadorfilas.');" ></td>'.
                        '<td class="tdmod" hidden><input type="number" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'"></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  ondblclick="saldarfactura('.$contadorfilas.')" readonly></td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$f->UUID.'" readonly>'.$f->UUID.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$f->Serie.'" readonly>'.$f->Serie.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$f->Folio.'" readonly>'.$f->Folio.'</td>'.
                        '<td class="tdmod"><input type="text" class="form-control equivalenciafacturapartida" name="equivalenciafacturapartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" readonly></td>'.
                        '<td class="tdmod">'.
                            '<select name="objimpuestofacturapartida[]" class="form-control divorinputmodxl objimpuestofacturapartida select2" style="width:100% !important;height: 28px !important;" required>'.
                                $selectobjetosimp.
                            '</select>'.
                        '</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$f->Moneda.'" readonly>'.$f->Moneda.'</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$f->TipoCambio.'" readonly>'.$f->TipoCambio.'</td>'.
                        '<td class="tdmod">'.
                            '<div class="row divorinputmodl">'.
                                '<div class="col-md-2">'.
                                    '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                                '</div>'.
                                '<div class="col-md-10">'.
                                    '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$f->MetodoPago.'" readonly>'.
                                '</div>'.
                            '</div>'.
                        '</td>'.
                        '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$numeroparcialidades.'" readonly>'.$numeroparcialidades.'</td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                        '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '</tr>';
                    $contadorfilas++;
                }
            }
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
            'rfc' => $rfc,
            'claveformapago' => $claveformapago,
            'formapago' => $formapago,
            'claveregimenfiscalreceptor' => $claveregimenfiscalreceptor,
            'regimenfiscalreceptor' => $regimenfiscalreceptor,
            'filasfacturas' => $filasfacturas
        );
        return response()->json($data);
    }

    //obtener bancos
    public function cuentas_por_cobrar_obtener_bancos(Request $request){
        if($request->ajax()){
            $data = Banco::where('Status', 'ALTA')->orderBy("Numero", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarbanco('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener banco por numero
    public function cuentas_por_cobrar_obtener_banco_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existebanco = Banco::where('Numero', $request->numerobanco)->where('Status', 'ALTA')->count();
        if($existebanco > 0){
            $banco = Banco::where('Numero', $request->numerobanco)->where('Status', 'ALTA')->first();
            $numero = $banco->Numero;
            $nombre = $banco->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }

    //obtener facturs clientes
    public function cuentas_por_cobrar_obtener_facturas_cliente(Request $request){
        $facturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->orderBy('Folio', 'DESC')->get();
        $numerofacturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->count();
        $filasfacturas= '';
        $contadorfilas = 0;
        if($numerofacturas > 0){
            $objetosimp = c_ObjetoImp::all();
            $selectobjetosimp = "<option selected disabled hidden>Selecciona</option>";
            foreach($objetosimp as $oi){
                if($oi->Numero == 2){
                    $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.' selected>'.$oi->Descripcion.'</option>';
                }else{
                    $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.'>'.$oi->Descripcion.'</option>';
                }
            }
            foreach($facturas as $f){
                $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($f->Iva, $f->SubTotal);
                $tipooperacion = $request->tipooperacion;
                $numparcialidades = CuentaXCobrarDetalle::where('Factura', $f->Factura)->where('Abono', '>', 0)->count();
                $numeroparcialidades = 1;
                if($numparcialidades > 0){
                    $numeroparcialidades = $numeroparcialidades + $numparcialidades;
                }
                //detalles factura
                $filasfacturas= $filasfacturas.
                '<tr class="filasfacturas" id="filafactura'.$contadorfilas.'">'.
                    '<td class="tdmod"><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$f->Factura.'" readonly>'.$f->Factura.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($f->Fecha).'" readonly>'.Helpers::fecha_espanol($f->Fecha).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$f->Plazo.'" readonly>'.$f->Plazo.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($f->Fecha)->addDays($f->Plazo)->toDateTimeString()).'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');calculartotal('.$contadorfilas.');" ></td>'.
                    '<td class="tdmod" hidden><input type="number" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'"></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/"  ondblclick="saldarfactura('.$contadorfilas.')" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$f->UUID.'" readonly>'.$f->UUID.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$f->Serie.'" readonly>'.$f->Serie.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$f->Folio.'" readonly>'.$f->Folio.'</td>'.
                    '<td class="tdmod"><input type="text" class="form-control equivalenciafacturapartida" name="equivalenciafacturapartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" readonly></td>'.
                    '<td class="tdmod">'.
                        '<select name="objimpuestofacturapartida[]" class="form-control divorinputmodxl objimpuestofacturapartida select2" style="width:100% !important;height: 28px !important;" required>'.
                            $selectobjetosimp.
                        '</select>'.
                    '</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$f->Moneda.'" readonly>'.$f->Moneda.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$f->TipoCambio.'" readonly>'.$f->TipoCambio.'</td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodl">'.
                            '<div class="col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-md-10">'.
                                '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$f->MetodoPago.'" readonly>'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$numeroparcialidades.'" readonly>'.$numeroparcialidades.'</td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                    '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($f->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
                '</tr>';
                $contadorfilas++;
            }
        }
        $data = array(
            "filasfacturas" => $filasfacturas,
        );
        return response()->json($data);
    }

    //obtener codifos postales
    public function cuentas_por_cobrar_obtener_codigos_postales(Request $request){
        if($request->ajax()){
            $data = CodigoPostal::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarlugarexpedicion(\''.$data->Clave .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener lugar expedicion por clave
    public function cuentas_por_cobrar_obtener_lugar_expedicion_por_clave(Request $request){
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

    //obtener regimenes fiscales
    public function cuentas_por_cobrar_obtener_regimenes_fiscales(Request $request){
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
    public function cuentas_por_cobrar_obtener_regimen_fiscal_por_clave(Request $request){
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

    //obtener tipos relacion
    public function cuentas_por_cobrar_obtener_tipos_relacion(Request $request){
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

    //obtener tipo relacion por clave
    public function cuentas_por_cobrar_obtener_tipo_relacion_por_clave(Request $request){
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
    public function cuentas_por_cobrar_obtener_formas_pago(Request $request){
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
    public function cuentas_por_cobrar_obtener_forma_pago_por_clave(Request $request){
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
    //obtener
    public function cuentas_por_cobrar_obtener_usos_cfdi(Request $request){
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

    //obtener por clave
    public function cuentas_por_cobrar_obtener_uso_cfdi_por_clave(Request $request){
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

    //obtener
    public function cuentas_por_cobrar_obtener_exportaciones(Request $request){
        if($request->ajax()){
            $data = c_Exportacion::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarexportacion(\''.$data->Clave .'\',\''.$data->Descripcion .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener por clave
    public function cuentas_por_cobrar_obtener_exportacion_por_clave(Request $request){
        $clave = '';
        $descripcion = '';
        $existeexportacion = c_Exportacion::where('Clave', $request->claveexportacion)->count();
        if($existeexportacion > 0){
            $exportacion = c_Exportacion::where('Clave', $request->claveexportacion)->first();
            $clave = $exportacion->Clave;
            $descripcion = $exportacion->Descripcion;
        }
        $data = array(
            'clave' => $clave,
            'descripcion' => $descripcion
        );
        return response()->json($data);
    }

    //obtener
    public function cuentas_por_cobrar_obtener_regimenes_fiscales_receptor(Request $request){
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
    public function cuentas_por_cobrar_obtener_regimenfiscalreceptor_por_clave(Request $request){
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

    //obtener metodos pago
    public function cuentas_por_cobrar_obtener_metodos_pago(Request $request){
        if($request->ajax()){
            $fila = $request->fila;
            $data = MetodoPago::query();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data) use ($fila){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarmetodopago(\''.$data->Clave .'\',\''.$data->Nombre .'\','.$fila.')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener facturas
    public function cuentas_por_cobrar_obtener_facturas(Request $request){
        if($request->ajax()){
            $arrayfacturasseleccionadas = Array();
            foreach(explode(",", $request->stringfacturasseleccionadas) as $factura){
                array_push($arrayfacturasseleccionadas, $factura);
            }
            $data = Factura::where('Cliente', $request->numerocliente)
                                ->whereNotIn('Factura', $arrayfacturasseleccionadas)
                                ->where('Status', 'POR COBRAR')
                                ->orderBy('Folio', 'DESC')
                                ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfactura('.$data->Folio.',\''.$data->Factura .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->addColumn('Fecha', function($data){
                        return Helpers::fecha_espanol($data->Fecha);
                    })
                    ->addColumn('Items', function($data){
                        return FacturaDetalle::where('Factura', $data->Factura)->count();
                    })
                    ->addColumn('Total', function($data){
                        return Helpers::convertirvalorcorrecto($data->Total);
                    })
                    ->addColumn('Abonos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Abonos);
                    })
                    ->addColumn('Descuentos', function($data){
                        return Helpers::convertirvalorcorrecto($data->Descuentos);
                    })
                    ->addColumn('Saldo', function($data){
                        return Helpers::convertirvalorcorrecto($data->Saldo);
                    })
                    ->rawColumns(['operaciones','Fecha','Total'])
                    ->make(true);
        }
    }

    //obtener factura
    public function cuentas_por_cobrar_obtener_factura(Request $request){
        $factura = Factura::where('Factura', $request->Factura)->first();
        $porcentajeiva = Helpers::calcular_porcentaje_iva_aritmetico($factura->Iva, $factura->SubTotal);
        $tipooperacion = $request->tipooperacion;
        $numeroparcialidades = 1;
        $numparcialidades = CuentaXCobrarDetalle::where('Factura', $request->Factura)->where('Abono', '>', 0)->count();
        if($numparcialidades > 0){
            $numeroparcialidades = $numeroparcialidades + $numparcialidades;
        }
        $objetosimp = c_ObjetoImp::all();
        $selectobjetosimp = "<option selected disabled hidden>Selecciona</option>";
        foreach($objetosimp as $oi){
            if($oi->Numero == 2){
                $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.' selected>'.$oi->Descripcion.'</option>';
            }else{
                $selectobjetosimp = $selectobjetosimp.'<option value='.$oi->Clave.'>'.$oi->Descripcion.'</option>';
            }
        }
        //detalles factura
        $filafactura = '';
        $filafactura= $filafactura.
        '<tr class="filasfacturas" id="filafactura'.$request->contadorfilas.'">'.
            '<td class="tdmod"><div class="btn btn-danger btn-xs btneliminarfilafactura" onclick="eliminarfilafactura('.$request->contadorfilas.')" >X</div><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="'.$tipooperacion.'" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($factura->Fecha).'" readonly>'.Helpers::fecha_espanol($factura->Fecha).'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$factura->Plazo.'" readonly>'.$factura->Plazo.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$request->contadorfilas.');calculartotal('.$request->contadorfilas.');" ></td>'.
            '<td class="tdmod" hidden><input type="number" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'"></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$factura->UUID.'" readonly>'.$factura->UUID.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$factura->Serie.'" readonly>'.$factura->Serie.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$factura->Folio.'" readonly>'.$factura->Folio.'</td>'.
            '<td class="tdmod"><input type="text" class="form-control equivalenciafacturapartida" name="equivalenciafacturapartida[]" value="'.Helpers::convertirvalorcorrecto(1).'" readonly></td>'.
            '<td class="tdmod">'.
                '<select name="objimpuestofacturapartida[]" class="form-control divorinputmodxl objimpuestofacturapartida select2" style="width:100% !important;height: 28px !important;" required>'.
                    $selectobjetosimp.
                '</select>'.
            '</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$factura->Moneda.'" readonly>'.$factura->Moneda.'</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$factura->TipoCambio.'" readonly>'.$factura->TipoCambio.'</td>'.
            '<td class="tdmod">'.
                '<div class="row divorinputmodl">'.
                    '<div class="col-md-2">'.
                        '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$request->contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                    '</div>'.
                    '<div class="col-md-10">'.
                        '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$factura->MetodoPago.'" readonly>'.
                    '</div>'.
                '</div>'.
            '</td>'.
            '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$numeroparcialidades.'" readonly>'.$numeroparcialidades.'</td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto(0).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
            '<td class="tdmod"><input type="number" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly></td>'.
        '</tr>';
        $data = array(
            "factura" => $factura,
            "filafactura" => $filafactura,
        );
        return response()->json($data);
    }

    //obtener folios notas
    public function cuentas_por_cobrar_obtener_folios_fiscales(Request $request){
        if($request->ajax()){
            $contarseriesasignadasausuario = User_Rel_Serie::where('user_id', Auth::user()->id)->where('documento_serie', 'PAGOS')->count();
            if($contarseriesasignadasausuario > 0){
                $data = DB::table('user_rel_series as urs')
                ->join('Folios Comprobantes Pagos as fcf', 'urs.serie_id', '=', 'fcf.Numero')
                ->select('urs.id', 'fcf.Numero', 'fcf.Serie', 'fcf.Esquema', 'fcf.Status')
                ->where('fcf.Status', 'ALTA')
                ->where('urs.user_id', Auth::user()->id)
                ->where('urs.documento_serie', 'PAGOS')
                ->orderby('fcf.Numero', 'DESC')
                ->get();
            }else{
                $data = FolioComprobantePago::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
            }
                return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarfoliofiscal(\''.$data->Serie.'\',\''.$data->Esquema.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }

    //obtener datos folio seleccionado
    public function cuentas_por_cobrar_obtener_ultimo_folio_serie_seleccionada(Request $request){
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXCobrar', $request->Serie);
        return response()->json($folio);
    }

    //altas
    public function cuentas_por_cobrar_guardar(Request $request){
        ini_set('max_input_vars','20000' );
        //obtener el ultimo id de la tabla
        $folio = Helpers::ultimofolioserietablamodulos('App\CuentaXCobrar', $request->serie);
        //INGRESAR DATOS A TABLA
        $pago = $folio.'-'.$request->serie;
		$CuentaXCobrar = new CuentaXCobrar;
		$CuentaXCobrar->Pago=$pago;
		$CuentaXCobrar->Serie=$request->serie;
		$CuentaXCobrar->Folio=$folio;
        $CuentaXCobrar->Fecha=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXCobrar->FechaPago=Carbon::parse($request->fechaaplicacionpagos)->toDateTimeString();
        $CuentaXCobrar->Cliente=$request->numerocliente;
		$CuentaXCobrar->Banco=$request->numerobanco;
        $CuentaXCobrar->Esquema=$request->esquema;
        $CuentaXCobrar->Abono=$request->total;
        $CuentaXCobrar->Anotacion=$request->anotacion;
        $CuentaXCobrar->Moneda=$request->moneda;
        $CuentaXCobrar->TipoCambio=$request->pesosmoneda;
        $CuentaXCobrar->EmisorRfc=$request->emisorrfc;
        $CuentaXCobrar->EmisorNombre=$request->emisornombre;
        $CuentaXCobrar->LugarExpedicion=$request->lugarexpedicion;
        $CuentaXCobrar->RegimenFiscal=$request->claveregimenfiscal;
        $CuentaXCobrar->ReceptorRfc=$request->receptorrfc;
        $CuentaXCobrar->ReceptorNombre=$request->receptornombre;
        $CuentaXCobrar->FormaPago=$request->claveformapago;
        $CuentaXCobrar->UsoCfdi=$request->claveusocfdi;
        $CuentaXCobrar->Exportacion=$request->claveexportacion;
        $CuentaXCobrar->RegimenFiscalReceptor=$request->claveregimenfiscalreceptor;
        $CuentaXCobrar->Hora=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXCobrar->Status="ALTA";
        $CuentaXCobrar->Usuario=Auth::user()->user;
        $CuentaXCobrar->Periodo=$this->periodohoy;
        $CuentaXCobrar->save();
        //modificar saldo cliente
        $SaldoCliente = Cliente::where('Numero', $request->numerocliente)->first();
        $NuevoSaldoCliente = $SaldoCliente->Saldo - $request->total;
        Cliente::where('Numero', $request->numerocliente)
        ->update([
            'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldoCliente)
        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXC";
        $BitacoraDocumento->Movimiento = $pago;
        $BitacoraDocumento->Aplicacion = "ALTA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = "ALTA";
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        //INGRESAR DATOS A TABLA DETALLES
        $item = 1;
        foreach ($request->facturaaplicarpartida as $key => $factura){
            if($request->abonopesosfacturapartida [$key] > Helpers::convertirvalorcorrecto(0)){
                $CuentaXCobrarDetalle=new CuentaXCobrarDetalle;
                $CuentaXCobrarDetalle->Pago = $pago;
                $CuentaXCobrarDetalle->Fecha = Carbon::parse($request->fecha)->toDateTimeString();
                $CuentaXCobrarDetalle->Cliente = $request->numerocliente;
                $CuentaXCobrarDetalle->Factura = $factura;
                $CuentaXCobrarDetalle->Abono = $request->abonopesosfacturapartida [$key];
                $CuentaXCobrarDetalle->idDocumento = $request->uuidfacturapartida [$key];
                $CuentaXCobrarDetalle->Serie = $request->seriefacturapartida [$key];
                $CuentaXCobrarDetalle->Folio = $request->foliofacturapartida [$key];
                $CuentaXCobrarDetalle->Equivalencia = $request->equivalenciafacturapartida [$key];
                $CuentaXCobrarDetalle->ObjetoImp = $request->objimpuestofacturapartida  [$key];
                $CuentaXCobrarDetalle->MonedaDR = $request->monedadrfacturapartida [$key];
                $CuentaXCobrarDetalle->TipoCambioDR = $request->tipocambiodrfacturapartida [$key];
                $CuentaXCobrarDetalle->MetodoDePagoDR = $request->metodopagodrfacturapartida [$key];
                $CuentaXCobrarDetalle->NumParcialidad = $request->numparcialidadfacturapartida [$key];
                $CuentaXCobrarDetalle->ImpSaldoAnt = $request->impsaldoantfacturapartida [$key];
                $CuentaXCobrarDetalle->ImpPagado = $request->imppagadofacturapartida [$key];
                $CuentaXCobrarDetalle->ImpSaldoInsoluto = $request->impsaldoinsolutofacturapartida [$key];
                $CuentaXCobrarDetalle->Item = $item;
                $CuentaXCobrarDetalle->save();
                $item++;
                //modificar abonos y saldo en factura
                $Factura = Factura::where('Factura', $factura)->first();
                $NuevoAbono = $Factura->Abonos + $request->abonopesosfacturapartida [$key];
                $NuevoSaldo = $request->saldofacturapartida [$key];
                if($request->saldofacturapartida [$key] == Helpers::convertirvalorcorrecto(0)){
                    $Status = "LIQUIDADO";
                }else{
                    $Status = "POR COBRAR";
                }
                //Modificar Factura
                Factura::where('Factura', $factura)
                ->update([
                    'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                    'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                    'Status' => $Status
                ]);
            }
        }
    	return response()->json($CuentaXCobrar);
    }

    //comprobar baja de documento
    public function cuentas_por_cobrar_comprobar_baja(Request $request){
        $CuentaXCobrar = CuentaXCobrar::where('Pago', $request->cxcdesactivar)->first();
        $resultadofechas = Helpers::compararanoymesfechas($CuentaXCobrar->Fecha);
        $data = array (
            'resultadofechas' => $resultadofechas,
            'Status' => $CuentaXCobrar->Status
        );
        return response()->json($data);
    }

    //bajas
    public function cuentas_por_cobrar_baja(Request $request){
        $CuentaXCobrar = CuentaXCobrar::where('Pago', $request->cxcdesactivar)->first();
        //modificar saldo cliente
        $SaldoCliente = Cliente::where('Numero', $CuentaXCobrar->Cliente)->first();
        $NuevoSaldoCliente = $SaldoCliente->Saldo + $CuentaXCobrar->Abono;
        Cliente::where('Numero', $CuentaXCobrar->Cliente)
        ->update([
            'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldoCliente)
        ]);
        //cambiar status y colocar valores en 0
        $MotivoBaja = $request->motivobaja.', '.Helpers::fecha_exacta_accion_datetimestring().', '.Auth::user()->user;
        CuentaXCobrar::where('Pago', $request->cxcdesactivar)
                ->update([
                    'MotivoBaja' => $MotivoBaja,
                    'Status' => 'BAJA',
                    'Abono' => '0.000000'
                ]);
        $detalles = CuentaXCobrarDetalle::where('Pago', $request->cxcdesactivar)->get();
        foreach($detalles as $detalle){
            //restar abono de la factura
            $Factura = Factura::where('Factura', $detalle->Factura)->first();
            $NuevoAbono = $Factura->Abonos - $detalle->Abono;
            $NuevoSaldo = $Factura->Saldo + $detalle->Abono;
            Factura::where('Factura', $detalle->Factura)
                        ->update([
                            'Abonos' => Helpers::convertirvalorcorrecto($NuevoAbono),
                            'Saldo' => Helpers::convertirvalorcorrecto($NuevoSaldo),
                            'Status' => "POR COBRAR"
                        ]);
            //tabla cuenta x pgar detalle
            CuentaXCobrarDetalle::where('Pago', $detalle->Pago)
                            ->where('Factura', $detalle->Factura)
                            ->update([
                                'Abono' => '0.000000'
                            ]);
        }
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXC";
        $BitacoraDocumento->Movimiento = $request->cxcdesactivar;
        $BitacoraDocumento->Aplicacion = "BAJA";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CuentaXCobrar->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
        return response()->json($CuentaXCobrar);
    }

    //obtener registro
    public function cuentas_por_cobrar_obtener_cuenta_por_cobrar(Request $request){
        $cuentaxcobrar = CuentaXCobrar::where('Pago', $request->cxcmodificar)->first();
        $numerocuentaxcobrardetalle = CuentaXCobrarDetalle::where('Pago', $request->cxcmodificar)->count();
        $cuentaxcobrardetalle = CuentaXCobrarDetalle::where('Pago', $request->cxcmodificar)->get();
        $cliente = Cliente::where('Numero', $cuentaxcobrar->Cliente)->first();
        $regimenfiscal = c_RegimenFiscal::where('Clave', $cuentaxcobrar->RegimenFiscal)->first();
        $tiporelacion = c_TipoRelacion::where('Numero', 1)->first();
        $formapago = FormaPago::where('Clave', $cuentaxcobrar->FormaPago)->first();
        $usocfdi = UsoCFDI::where('Clave', $cuentaxcobrar->UsoCfdi)->first();
        $exportacion = c_Exportacion::where('Clave', $cuentaxcobrar->Exportacion)->first();
        $regimenfiscalreceptor = c_RegimenFiscal::where('Clave', $cuentaxcobrar->RegimenFiscalReceptor)->first();
        $banco = Banco::where('Numero', $cuentaxcobrar->Banco)->first();
        $filasdetallecuentasporcobrar = '';
        $contadorfilas = 0;
        $numerodetalle = 1;
        $contadorproductos = 0;
        $arrayfacturas = array();
        if($numerocuentaxcobrardetalle > 0){
            foreach($cuentaxcobrardetalle as $cxcd){
                $ObjetoImp = c_ObjetoImp::where('Clave', $cxcd->ObjetoImp)->first();
                array_push($arrayfacturas, $cxcd->Factura);
                $factura = Factura::where('Factura', $cxcd->Factura)->first();
                $encabezadostablaacopiar = '#,Factura,Fecha,Plazo,Vence,Total $,Abonos $,Notas Crédito $,Abono,Saldo $,idDocumento,Serie,Folio,MonedaDR,TipoCambioDR,MetodoDePagoDR,NumParcialidad,ImpSaldoAnt,ImpPagado,ImpSaldoInsoluto';
                $clasecolumnaobtenervalor = '.numerodetalle,.facturaaplicarpartida,.fechafacturapartida,.plazofacturapartida,.vencefacturapartida,.totalpesosfacturapartida,.abonosfacturapartida,.notascreditofacturapartida,.abonopesosfacturapartida,.saldofacturapartida,.uuidfacturapartida,.seriefacturapartida,.foliofacturapartida,.monedadrfacturapartida,.tipocambiodrfacturapartida,.metodopagodrfacturapartida,.numparcialidadfacturapartida,.impsaldoantfacturapartida,.imppagadofacturapartida,.impsaldoinsolutofacturapartida';
                $filasdetallecuentasporcobrar= $filasdetallecuentasporcobrar.
                '<tr class="filasfacturas filafactura'.$contadorfilas.'" id="filafactura'.$contadorfilas.'">'.
                    '<td class="tdmod"><input type="hidden" class="form-control divorinputmodsm numerodetalle" name="numerodetalle[]" value="'.$numerodetalle.'" readonly><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly>'.$numerodetalle.'</td>'.
                    '<td class="tdmod" ondblclick="construirtabladinamicaporfila('.$contadorfilas.',\'tr.filasfacturas\',\''.$encabezadostablaacopiar.'\',\''.$clasecolumnaobtenervalor.'\')"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control fechafacturapartida" name="fechafacturapartida[]" value="'.Helpers::fecha_espanol($factura->Fecha).'" readonly>'.Helpers::fecha_espanol($factura->Fecha).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control plazofacturapartida" name="plazofacturapartida[]" value="'.$factura->Plazo.'" readonly>'.$factura->Plazo.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control vencefacturapartida" name="vencefacturapartida[]" value="'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'" readonly>'.Helpers::fecha_espanol(Carbon::parse($factura->Fecha)->addDays($factura->Plazo)->toDateTimeString()).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd totalpesosfacturapartida" name="totalpesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Total).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($factura->Total).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonosfacturapartida" name="abonosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Abonos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($factura->Abonos).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd notascreditofacturapartida" name="notascreditofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Descuentos).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($factura->Descuentos).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd abonopesosfacturapartida" name="abonopesosfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->Abono).'" data-parsley-max="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" onchange="formatocorrectoinputcantidades(this);calcularnuevosaldo('.$contadorfilas.');calculartotal('.$contadorfilas.');" >'.Helpers::convertirvalorcorrecto($factura->Saldo).'</td>'.
                    '<td class="tdmod" hidden><input type="hidden" class="form-control divorinputmodmd saldofacturapartidadb" name="saldofacturapartidadb[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'">'.Helpers::convertirvalorcorrecto($factura->Saldo).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd saldofacturapartida" name="saldofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($factura->Saldo).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($factura->Saldo).'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control uuidfacturapartida" name="uuidfacturapartida[]" value="'.$cxcd->idDocumento.'" readonly>'.$cxcd->idDocumento.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control seriefacturapartida" name="seriefacturapartida[]" value="'.$cxcd->Serie.'" readonly>'.$cxcd->Serie.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control foliofacturapartida" name="foliofacturapartida[]" value="'.$cxcd->Folio.'" readonly>'.$cxcd->Folio.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control equivalenciafacturapartida" name="equivalenciafacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->Equivalencia).'" readonly>'.Helpers::convertirvalorcorrecto($cxcd->Equivalencia).'</td>'.
                    '<td class="tdmod">'.$cxcd->ObjetoImp.' - '.$ObjetoImp->Descripcion.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control monedadrfacturapartida" name="monedadrfacturapartida[]" value="'.$cxcd->MonedaDR.'" readonly>'.$cxcd->MonedaDR.'</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control tipocambiodrfacturapartida" name="tipocambiodrfacturapartida[]" value="'.$cxcd->TipoCambioDR.'" readonly>'.$cxcd->TipoCambioDR.'</td>'.
                    '<td class="tdmod">'.
                        '<div class="row divorinputmodl" hidden>'.
                            '<div class="col-md-2">'.
                                '<div class="btn bg-blue btn-xs waves-effect" data-toggle="tooltip" title="Cambiar Método de Pago" onclick="listarmetodospago('.$contadorfilas.');" ><i class="material-icons">remove_red_eye</i></div>'.
                            '</div>'.
                            '<div class="col-md-10">'.
                                '<input type="text" class="form-control divorinputmodsm metodopagodrfacturapartida" name="metodopagodrfacturapartida[]" value="'.$cxcd->MetodoDePagoDR.'" readonly>'.
                            '</div>'.
                        '</div>'.
                        $cxcd->MetodoDePagoDR.
                    '</td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control numparcialidadfacturapartida" name="numparcialidadfacturapartida[]" value="'.$cxcd->NumParcialidad.'" readonly>'.$cxcd->NumParcialidad.'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoantfacturapartida" name="impsaldoantfacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd imppagadofacturapartida" name="imppagadofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpPagado).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($cxcd->ImpPagado).'</td>'.
                    '<td class="tdmod"><input type="hidden" step="0.'.$this->numerocerosconfiguradosinputnumberstep.'" class="form-control divorinputmodmd impsaldoinsolutofacturapartida" name="impsaldoinsolutofacturapartida[]" value="'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto).'" data-parsley-decimalesconfigurados="/^[0-9]+[.]+[0-9]{'.$this->numerodecimales.'}$/" readonly>'.Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto).'</td>'.
                '</tr>';
                $contadorfilas++;
                $contadorproductos++;
                $numerodetalle++;
            }
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($cuentaxcobrar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                if($cuentaxcobrar->UUID != ""){
                    $modificacionpermitida = 0;
                }else{
                    $modificacionpermitida = 1;
                }
            }
        }
        if(Auth::user()->role_id != 1){
            if($cuentaxcobrar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $resultadofechas = Helpers::compararanoymesfechas($cuentaxcobrar->Fecha);
                if($resultadofechas != ''){
                    $modificacionpermitida = 0;
                }else{
                    if($cuentaxcobrar->UUID != ""){
                        $modificacionpermitida = 0;
                    }else{
                        $modificacionpermitida = 1;
                    }
                }
            }
        }
        $data = array(
            'cuentaxcobrar' => $cuentaxcobrar,
            'cuentaxcobrardetalle' => $cuentaxcobrardetalle,
            'numerocuentaxcobrardetalle' => $numerocuentaxcobrardetalle,
            'contadorproductos' => $contadorproductos,
            'contadorfilas' => $contadorfilas,
            'fecha' => Helpers::formatoinputdatetime($cuentaxcobrar->Fecha),
            'fechapago' => Helpers::formatoinputdatetime($cuentaxcobrar->FechaPago),
            "fechasdisponiblesenmodificacion" => Helpers::obtenerfechasdisponiblesenmodificacion($cuentaxcobrar->Fecha),
            'cliente' => $cliente,
            'regimenfiscal' => $regimenfiscal,
            'tiporelacion' => $tiporelacion,
            'formapago' => $formapago,
            'usocfdi' => $usocfdi,
            'exportacion' => $exportacion,
            'regimenfiscalreceptor' => $regimenfiscalreceptor,
            'banco' => $banco,
            'filasdetallecuentasporcobrar' => $filasdetallecuentasporcobrar,
            'arrayfacturas' => $arrayfacturas,
            'abonototal' => Helpers::convertirvalorcorrecto($cuentaxcobrar->Abono),
            'tipocambio' => Helpers::convertirvalorcorrecto($cuentaxcobrar->TipoCambio),
            "modificacionpermitida" => $modificacionpermitida
        );
        return response()->json($data);
    }
    //cambios
    public function cuentas_por_cobrar_guardar_modificacion(Request $request){
        ini_set('max_input_vars','20000' );
        //INGRESAR DATOS A TABLA
        $cuentaxcobrar = $request->folio.'-'.$request->serie;
		$CuentaXCobrar = CuentaXCobrar::where('Pago', $cuentaxcobrar)->first();
        //modificar
        CuentaXCobrar::where('Pago', $cuentaxcobrar)
        ->update([
            'Fecha' => Carbon::parse($request->fecha)->toDateTimeString(),
            'Hora' => Carbon::parse($request->fecha)->toDateTimeString(),
            'FechaPago' => Carbon::parse($request->fechaaplicacionpagos)->toDateTimeString(),
            'Banco' => $request->numerobanco,
            'Anotacion' => $request->anotacion,
            'Moneda' => $request->moneda,
            'TipoCambio' => $request->pesosmoneda,
            'LugarExpedicion' => $request->lugarexpedicion,
            'RegimenFiscal' => $request->claveregimenfiscal,
            'FormaPago' => $request->claveformapago,
            'UsoCfdi' => $request->claveusocfdi,
            'Exportacion' => $request->claveexportacion,
            'RegimenFiscalReceptor' => $request->claveregimenfiscalreceptor

        ]);
        //INGRESAR LOS DATOS A LA BITACORA DE DOCUMENTO
        $BitacoraDocumento = new BitacoraDocumento;
        $BitacoraDocumento->Documento = "CXC";
        $BitacoraDocumento->Movimiento = $cuentaxcobrar;
        $BitacoraDocumento->Aplicacion = "CAMBIO";
        $BitacoraDocumento->Fecha = Helpers::fecha_exacta_accion_datetimestring();
        $BitacoraDocumento->Status = $CuentaXCobrar->Status;
        $BitacoraDocumento->Usuario = Auth::user()->user;
        $BitacoraDocumento->Periodo = $this->periodohoy;
        $BitacoraDocumento->save();
    	return response()->json($CuentaXCobrar);
    }
    //buscar folio on key up
    public function cuentas_por_cobrar_buscar_folio_string_like(Request $request){
        if($request->ajax()){
            $string = $request->string;
            $data = VistaCuentaPorCobrar::orderBy('Folio', 'ASC')->get();
            return DataTables::of($data)
                ->addColumn('Abono', function($data){
                    return Helpers::convertirvalorcorrecto($data->Abono);
                })
                ->make(true);
        }
    }

    //generar documento PDF
    public function cuentas_por_cobrar_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        //primero eliminar todos los archivos xml generados
        Helpers::eliminararchivosxmlsgenerados();
        //primero eliminar todos los archivos zip
        Helpers::eliminararchivoszipgenerados();
        if($request->imprimirdirectamente == 1){
            $cuentasporcobrar = CuentaXCobrar::where('Pago', $request->arraypdf)->get();
        }else{
            $tipogeneracionpdf = $request->tipogeneracionpdf;
            if($tipogeneracionpdf == 0){
                $cuentasporcobrar = CuentaXCobrar::whereIn('Pago', $request->arraypdf)->orderBy('Folio', 'ASC')->take(150)->get();
            }else{
                $fechainiciopdf = date($request->fechainiciopdf);
                $fechaterminacionpdf = date($request->fechaterminacionpdf);
                if ($request->has("seriesdisponiblesdocumento")){
                    $cuentasporcobrar = CuentaXCobrar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->whereIn('Serie', $request->seriesdisponiblesdocumento)->orderBy('Folio', 'ASC')->take(150)->get();
                }else{
                    $cuentasporcobrar = CuentaXCobrar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(150)->get();
                }
            }
        }
        if($cuentasporcobrar->count() < 1){
            echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $arrayfiles = array();
        $arrayfilespdf = array();
        foreach ($cuentasporcobrar as $cxc){
            $data=array();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $abonoletras =  $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($facturadetalle->Total),
                    "objetoimpdetalle" => $cxcd->ObjetoImp,
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            }
            //obtener XML
            if($request->descargar_xml == 1){
                $factura = CuentaXCobrar::where('Pago', $cxc->Pago)->first();
                $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->where('IdFacturapi', '<>', NULL)->first();
                if($comprobante != null){
                    $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
                    $nombre_xml = "CuentaPorCobrarNo".$cxc->Pago.'##'.$cxc->UUID.'.xml';
                    Storage::disk('local2')->put($nombre_xml, $descargar_xml);
                    array_push($arrayfiles, $nombre_xml);
                }
            }
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $usocfdi = UsoCFDI::where('Clave', $cxc->UsoCfdi)->first();
            $exportacion = c_Exportacion::where('Clave', $cxc->Exportacion)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $banco = Banco::where('Numero', $cxc->Banco)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscalReceptor)->first();
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "regimenfiscalcliente" => $regimenfiscalcliente,
                        "estadocliente" => $estadocliente,
                        "banco"=> $banco,
                        "usocfdi" => $usocfdi,
                        "exportacion" => $exportacion,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            if($request->tipoformatocxc == 1){
                $pdf = PDF::loadView('registros.cuentasporcobrar.formato_poliza_ingreso_pdf_cuentasporcobrar', compact('data'))
                        ->setPaper('Letter')
                        ->setOption('footer-font-size', 7)
                        ->setOption('margin-left', 2)
                        ->setOption('margin-right', 2)
                        ->setOption('margin-bottom', 10);
            }else{
                $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
                        ->setPaper('Letter')
                        //->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
                        ->setOption('footer-center', 'Página [page] de [toPage]')
                        //->setOption('footer-right', ''.$fechaformato.'')
                        ->setOption('footer-font-size', 7)
                        ->setOption('margin-left', 2)
                        ->setOption('margin-right', 2)
                        ->setOption('margin-bottom', 10);
            }
            $ArchivoPDF = "PDF".$cxc->Pago.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($cuentasporcobrar as $cxco){
            $ArchivoPDF = "PDF".$cxco->Pago.".pdf";
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
                $pdfMerger->save("CuentasPorCobrar.pdf", "browser");//mostrarlos en el navegador
            }else{
                //carpeta donde se guardara el archivo zip
                $public_dir=public_path();
                // Zip File Name
                $zipFileName = 'CuentasPorCobrar.zip';
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

    //generacion de formato en PDF
    public function cuentas_por_cobrar_generar_pdfs_indiv($documento,$tipodocumento){
        $cuentasporcobrar = CuentaXCobrar::where('Pago', $documento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $abonoletras =  $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                if ($cxcd->Abono < $facturadetalle->Total) {
                    $totalFactura = $cxcd->Abono / 1.16;
                }else{
                    $totalFactura = $facturadetalle->SubTotal;
                }
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($totalFactura),
                    "objetoimpdetalle" => $cxcd->ObjetoImp,
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            }
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $usocfdi = UsoCFDI::where('Clave', $cxc->UsoCfdi)->first();
            $exportacion = c_Exportacion::where('Clave', $cxc->Exportacion)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $banco = Banco::where('Numero', $cxc->Banco)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscalReceptor)->first();
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "regimenfiscalcliente" => $regimenfiscalcliente,
                        "estadocliente" => $estadocliente,
                        "banco"=> $banco,
                        "usocfdi" => $usocfdi,
                        "exportacion" => $exportacion,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        if($tipodocumento == 1){
            $pdf = PDF::loadView('registros.cuentasporcobrar.formato_poliza_ingreso_pdf_cuentasporcobrar', compact('data'))
                    ->setPaper('Letter')
                    ->setOption('footer-font-size', 7)
                    ->setOption('margin-left', 2)
                    ->setOption('margin-right', 2)
                    ->setOption('margin-bottom', 10);
        }else{
            $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
                    ->setPaper('Letter')
                    //->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
                    ->setOption('footer-center', 'Página [page] de [toPage]')
                    //->setOption('footer-right', ''.$fechaformato.'')
                    ->setOption('footer-font-size', 7)
                    ->setOption('margin-left', 2)
                    ->setOption('margin-right', 2)
                    ->setOption('margin-bottom', 10);
        }
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function cuentas_por_cobrar_obtener_datos_envio_email(Request $request){
        $cuentaporcobrar = CuentaXCobrar::where('Pago', $request->documento)->first();
        $cliente = Cliente::where('Numero',$cuentaporcobrar->Cliente)->first();
        $email2cc = '';
        $email3cc = '';
        if($cliente->Email2 != '' || $cliente->Email2 != null){
            $email2cc = $cliente->Email2;
        }
        if($cliente->Email3 != '' || $cliente->Email3 != null){
            $email3cc = $cliente->Email3;
        }
        $data = array(
            'cuentaporcobrar' => $cuentaporcobrar,
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
    public function cuentas_por_cobrar_enviar_pdfs_email(Request $request){
        $cuentasporcobrar = CuentaXCobrar::where('Pago', $request->emaildocumento)->get();
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $abonoletras =  $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $cuentaporcobrardetalle = CuentaXCobrarDetalle::where('Pago', $cxc->Pago)->get();
            $datadetalle=array();
            $importepagado = 0;
            $importesaldoinsoluto = 0;
            foreach($cuentaporcobrardetalle as $cxcd){
                $importepagado = $importepagado + $cxcd->ImpPagado;
                $importesaldoinsoluto = $importesaldoinsoluto + $cxcd->ImpSaldoInsoluto;
                $clientedetalle = Cliente::where('Numero', $cxcd->Cliente)->first();
                $facturadetalle = Factura::where('Factura', $cxcd->Factura)->first();
                $metodopagofacturadetalle = MetodoPago::where('Clave', $facturadetalle->MetodoPago)->first();
                $datadetalle[]=array(
                    "clientedetalle"=> $clientedetalle,
                    "iddocumentodetalle" => $cxcd->idDocumento,
                    "facturadetalle" => $cxcd->Factura,
                    "fechadetalle" => Carbon::parse($facturadetalle->Fecha)->toDateString(),
                    "plazodetalle" => $facturadetalle->Plazo,
                    "vencedetalle" => Carbon::parse($facturadetalle->Fecha)->addDays($facturadetalle->Plazo)->toDateString(),
                    "totalfactura" => Helpers::convertirvalorcorrecto($facturadetalle->Total),
                    "objetoimpdetalle" => $cxcd->ObjetoImp,
                    "numparcialidaddetalle" => $cxcd->NumParcialidad,
                    "impsaldoantdetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoAnt),
                    "imppagadodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpPagado),
                    "impsaldoinsolutodetalle" => Helpers::convertirvalorcorrecto($cxcd->ImpSaldoInsoluto),
                    "tipocambiofacturadetalle" => Helpers::convertirvalorcorrecto($facturadetalle->TipoCambio),
                    "clavemetodopagodetalle" => $metodopagofacturadetalle->Clave,
                    "nombremetodopagodetalle" => $metodopagofacturadetalle->Nombre
                );
            }
            $cliente = Cliente::where('Numero', $cxc->Cliente)->first();
            $estadocliente = Estado::where('Clave', $cliente->Estado)->first();
            $formapago = FormaPago::where('Clave', $cxc->FormaPago)->first();
            $regimenfiscal = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscal)->first();
            $usocfdi = UsoCFDI::where('Clave', $cxc->UsoCfdi)->first();
            $exportacion = c_Exportacion::where('Clave', $cxc->Exportacion)->first();
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $formatter->conector = 'PESOS';
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
            $banco = Banco::where('Numero', $cxc->Banco)->first();
            $regimenfiscalcliente = c_RegimenFiscal::where('Clave', $cxc->RegimenFiscalReceptor)->first();
            $data[]=array(
                        "cuentaporcobrar"=>$cxc,
                        "fechaespanolcuentaporcobrar"=>Helpers::fecha_espanol($cxc->Fecha),
                        "abonocuentaporcobrar"=>Helpers::convertirvalorcorrecto($cxc->Abono),
                        "importepagado" =>Helpers::convertirvalorcorrecto($importepagado),
                        "importesaldoinsoluto" =>Helpers::convertirvalorcorrecto($importesaldoinsoluto),
                        "abonoletras"=>$abonoletras,
                        "formapago" => $formapago,
                        "comprobante" => $comprobante,
                        "comprobantetimbrado" => $comprobantetimbrado,
                        "regimenfiscal"=> $regimenfiscal,
                        "cliente" => $cliente,
                        "regimenfiscalcliente" => $regimenfiscalcliente,
                        "estadocliente" => $estadocliente,
                        "banco"=> $banco,
                        "usocfdi" => $usocfdi,
                        "exportacion" => $exportacion,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        if($request->tipoformato == 1){
            $pdf = PDF::loadView('registros.cuentasporcobrar.formato_poliza_ingreso_pdf_cuentasporcobrar', compact('data'))
                    ->setPaper('Letter')
                    ->setOption('footer-font-size', 7)
                    ->setOption('margin-left', 2)
                    ->setOption('margin-right', 2)
                    ->setOption('margin-bottom', 10);
        }else{
            $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
                    ->setPaper('Letter')
                    //->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
                    ->setOption('footer-center', 'Página [page] de [toPage]')
                    //->setOption('footer-right', ''.$fechaformato.'')
                    ->setOption('footer-font-size', 7)
                    ->setOption('margin-left', 2)
                    ->setOption('margin-right', 2)
                    ->setOption('margin-bottom', 10);
        }
        //obtener XML
        if($request->incluir_xml == 1){
            $cxc = CuentaXCobrar::where('Pago', $request->emaildocumento)->first();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $descargar_xml = $this->facturapi->Invoices->download_xml($comprobante->IdFacturapi); // stream containing the XML file or
            $nombre_xml = "CuentaPorCobrarNo".$cxc->Pago.'##'.$cxc->UUID.'.xml';
            Storage::disk('local')->put($nombre_xml, $descargar_xml);
            $url_xml = Storage::disk('local')->getAdapter()->applyPathPrefix($nombre_xml);
        }else{
            $url_xml = "";
        }
        try{
            $datosdocumento = CuentaXCobrar::where('Pago', $request->emaildocumento)->first();
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
            if (file_exists($url_xml) != false) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento,$url_xml) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento,$url_xml)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CuentaPorCobrarNo".$emaildocumento.".pdf")
                            ->attach($url_xml);
                });
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol', 'datosdocumento'), function($message) use ($nombre, $receptor, $arraycc, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($arraycc)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CuentaPorCobrarNo".$emaildocumento.".pdf");
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

    //exportar excel
    public function cuentas_por_cobrar_exportar_excel(Request $request){
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CuentasPorCobrar', Auth::user()->id);
        return Excel::download(new CuentasPorCobrarExport($configuraciones_tabla['campos_consulta'],$request->periodo), "cuentasporcobrar-".$request->periodo.".xlsx");
    }

    //guardar configuracion tabla
    public function cuentas_por_cobrar_guardar_configuracion_tabla(Request $request){
        if($request->string_datos_tabla_false == null){
            $string_datos_tabla_false = "todos los campos fueron seleccionados";
        }else{
            $string_datos_tabla_false = $request->string_datos_tabla_false;
        }
        $selectmultiple = '';
        foreach($request->selectfiltrosbusquedas as $campofiltro){
            $selectmultiple = $selectmultiple.",".$campofiltro;
        }
        $configuraciones_tabla = Helpers::obtenerconfiguraciontabla('CuentasPorCobrar', Auth::user()->id);
        if($configuraciones_tabla['contar_configuracion_tabla'] > 0){
            Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')->where('IdUsuario', Auth::user()->id)
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
            $Configuracion_Tabla->tabla='CuentasPorCobrar';
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
        return redirect()->route('cuentas_por_cobrar');
    }

    //verificar si se puede timbrar la factura
    public function cuentas_por_cobrar_verificar_si_continua_timbrado(Request $request){
        $CXC = CuentaXCobrar::where('Pago', $request->pago)->first();
        $data = array(
            'Esquema' => $CXC->Esquema,
            'Status' => $CXC->Status,
            'UUID' => $CXC->UUID
        );
        return response()->json($data);
    }
    //timbrar factura
    public function cuentas_por_cobrar_timbrar_pago(Request $request){
        $CXC = CuentaXCobrar::where('Pago', $request->pagotimbrado)->first();
        $detallescxc = CuentaXCobrarDetalle::where('Pago', $request->pagotimbrado)->get();
        $cliente = Cliente::where('Numero', $CXC->Cliente)->first();
        $arraydet = array();
        foreach($detallescxc as $dp){
            $abono = 0;
            $ivaAbonoParcial = 0;
            $factura = Factura::where('Factura',$dp->Factura)->first();
            if (($factura->Total-$dp->Abono) <= 0.1) {
                $abono = $factura->SubTotal;
            }else{
                $abono = $dp->Abono / 1.16;
                $abono = number_format(round($abono, 4), 4, '.', '');
                //$abono = number_format(round($dp->Abono, 2), 2, '.', '') - number_format(round($ivaAbonoParcial, 2), 2, '.', '');
            }
            //agregado para facturacion 4.0
            if($dp->ObjetoImp == '02'){//con impuestos
                $taxes = array(
                    array(
                        //"base" => Helpers::convertirvalorcorrecto($dp->Abono),
                        "base" => number_format(round($abono, 2), 2, '.', ''),
                        "type" => "IVA",
                        "rate" => 0.16
                    )
                );
            }else{//sin impuestos
                $taxes = [];
            }
            array_push($arraydet,   array(
                                        "uuid" => $dp->idDocumento, // UUID_de_factura_relacionada
                                        //"installment" => Helpers::convertirvalorcorrecto($dp->NumParcialidad),
                                        "installment" => number_format(round($dp->NumParcialidad, 2), 2, '.', ''),
                                        //"last_balance" => Helpers::convertirvalorcorrecto($dp->ImpSaldoAnt),
                                        "last_balance" => number_format(round($dp->ImpSaldoAnt, 2), 2, '.', ''),
                                        //"amount" => Helpers::convertirvalorcorrecto($dp->Abono),
                                        "amount" => number_format(round($dp->Abono, 2), 2, '.', ''),
                                        "currency" => $dp->MonedaDR,
                                        "folio_number" => $dp->Folio,
                                        "series" => $dp->Serie,
                                        //si taxability == 01 sin impuestos si es 02 con impuestos
                                        "taxability" => $dp->ObjetoImp, //c_ObjetoImp
                                        "taxes" => $taxes,
                                        /*
                                        "taxes" => array(
                                            array(
                                                "base" => Helpers::convertirvalorcorrecto($dp->Abono),
                                                "type" => "IVA",
                                                "rate" => 0.160000
                                            )
                                        )
                                        */
                                    )
            );
        }
        //CUENTA POR COBRAR
        $invoice = array(
            "type" => \Facturapi\InvoiceType::PAGO,
            "customer" => array(
                "legal_name" => $cliente->Nombre,
                "tax_id" => $cliente->Rfc,

                //se debe agregar para version 2.0 de facturapi que integrado el timbrado de cfdi 4.0
                //"tax_system" => $cliente->RegimenFiscal,
                "tax_system" => $CXC->RegimenFiscalReceptor,
                "address" =>
                    array(
                        "zip" => $cliente->CodigoPostal,
                    )
                //fin cfdi 4.0

            ),
            /*
            //se debe agregar para facturapi version 1.0
            "payments" => array(
                array(
                    "payment_form" => $CXC->FormaPago,
                    "currency" => $CXC->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($CXC->TipoCambio),
                    "date" => Helpers::formatoinputdatetime($CXC->FechaPago),
                    "related" => $arraydet
                )
            ),
            */

            //se debe agregar para facturapi version 2.0
            "complements" => array(
                //"type" => "P",
                array(
                    "type" => "pago",
                    "data" => array(
                        array(
                            "payment_form" => $CXC->FormaPago,
                            "currency" => $CXC->Moneda,
                            //"exchange" => Helpers::convertirvalorcorrecto($CXC->TipoCambio),
                            "exchange" => number_format(round($CXC->TipoCambio, 2), 2, '.', ''),
                            "date" => Helpers::formatoinputdatetime($CXC->FechaPago),
                            "related_documents" =>  $arraydet
                        )
                    )
                )
            ),

            "date" => Helpers::formatoinputdatetime($CXC->Fecha),
            "folio_number" => $CXC->Folio,
            "series" => $CXC->Serie,
        );
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
            $Comprobante->Comprobante = 'Pago';
            $Comprobante->Tipo = $new_invoice->type;
            //version 4.0
            $Comprobante->Version = '4.0';
            //version 3.3
            //$Comprobante->Version = '3.3';
            $Comprobante->Serie = $new_invoice->series;
            $Comprobante->Folio = $new_invoice->folio_number;
            $Comprobante->UUID = $new_invoice->uuid;
            $Comprobante->Fecha = Helpers::fecha_exacta_accion_datetimestring();
            $Comprobante->SubTotal = Helpers::convertirvalorcorrecto(0);
            $Comprobante->Descuento = Helpers::convertirvalorcorrecto(0);
            $Comprobante->Total = Helpers::convertirvalorcorrecto(0);
            $Comprobante->EmisorRfc = $CXC->EmisorRfc;
            $Comprobante->ReceptorRfc = $CXC->ReceptorRfc;
            $Comprobante->UsoCfdi = 'P01';
            $Comprobante->Moneda = 'XXX';
            $Comprobante->TipoCambio = Helpers::convertirvalorcorrecto(0);
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
            //Colocar UUID en cxc
            CuentaXCobrar::where('Pago', $request->pagotimbrado)
                            ->update([
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
    public function cuentas_por_cobrar_verificar_si_continua_baja_timbre(Request $request){
        $obtener_factura = '';
        $comprobante = '';
        $factura = CuentaXCobrar::where('Pago', $request->facturabajatimbre)->first();
        $existe_comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->count();
        if($existe_comprobante > 0){
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')->first();
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
    public function cuentas_por_cobrar_baja_timbre(Request $request){
        //cancelar timbre facturapi
        //con version 1.0 facturapi sin motivo de baja
        //$timbrecancelado = $this->facturapi->Invoices->cancel($request->iddocumentofacturapi);
        // con version 2.0 facturapi con motivo de baja
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
            $factura = CuentaXCobrar::where('Pago', $request->facturabajatimbre)->first();
            Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')
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
