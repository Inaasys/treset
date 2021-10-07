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
use Config;
use Mail;
use Facturapi\Facturapi;
use Storage;
use LynX39\LaraPdfMerger\Facades\PdfMerger;

class CuentasPorCobrarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
        //CONFIGURACIONES DE LA TABLA DEL CATALOGO O MODULO//
        $this->configuracion_tabla = Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')->first();
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
        //API FACTURAPI 
        $this->facturapi = new Facturapi( config('app.keyfacturapi') ); //
    }

    public function cuentas_por_cobrar(){
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
        $configuracion_tabla = $this->configuracion_tabla;
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
            $tipousuariologueado = Auth::user()->role_id;
            $periodo = $request->periodo;
            //$data = VistaCuentaPorCobrar::select($this->campos_consulta)->where('Periodo', $periodo)->orderBy('Fecha', 'DESC')->orderBy('Serie', 'ASC')->orderBy('Folio', 'DESC')->get();
            $data = VistaCuentaPorCobrar::select($this->campos_consulta)->where('Periodo', $periodo);
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
                ->addColumn('operaciones', function($data){
                    $operaciones = '<div class="dropdown">'.
                                        '<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.
                                            'OPERACIONES <span class="caret"></span>'.
                                        '</button>'.
                                        '<ul class="dropdown-menu">'.
                                            '<li><a href="javascript:void(0);" onclick="obtenerdatos(\''.$data->Pago .'\')">Cambios</a></li>'.
                                            '<li><a href="javascript:void(0);" onclick="desactivar(\''.$data->Pago .'\')">Bajas</a></li>'.
                                            '<li><a href="'.route('cuentas_por_cobrar_generar_pdfs_indiv',$data->Pago).'" target="_blank">Ver Documento PDF</a></li>'.
                                            '<li><a href="javascript:void(0);" onclick="enviardocumentoemail(\''.$data->Pago .'\')">Enviar Documento por Correo</a></li>'.
                                            //'<li><a href="javascript:void(0);" onclick="timbrarpago(\''.$data->Pago .'\')">Timbrar Pago</a></li>'.
                                            //'<li><a href="javascript:void(0);" onclick="cancelartimbre(\''.$data->Pago .'\')">Cancelar Timbre</a></li>'.
                                        '</ul>'.
                                    '</div>';
                    return $operaciones;
                })
                ->addColumn('Fecha', function($data){ return Carbon::parse($data->Fecha)->toDateTimeString(); })
                ->addColumn('Abono', function($data){ return $data->Abono; })
                ->addColumn('TipoCambio', function($data){ return $data->TipoCambio; })
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
            ->select('c.Numero', 'c.Nombre', 'c.Plazo', 'c.Rfc', 'c.Agente', 'c.Credito', 'c.Saldo', 'c.Status', 'c.Municipio', 'c.Tipo', 'fp.Clave AS ClaveFormaPago', 'fp.Nombre AS NombreFormaPago', 'mp.Clave AS ClaveMetodoPago', 'mp.Nombre AS NombreMetodoPago', 'uc.Clave AS ClaveUsoCfdi', 'uc.Nombre AS NombreUsoCfdi', 'p.Clave AS ClavePais', 'p.Nombre AS NombrePais')
            ->where('c.Status', 'ALTA')
            ->orderBy("Numero", "DESC")
            ->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre .'\','.$data->Plazo.',\''.$data->Rfc.'\',\''.$data->ClaveFormaPago.'\',\''.$data->NombreFormaPago.'\',\''.$data->ClaveMetodoPago.'\',\''.$data->NombreMetodoPago.'\',\''.$data->ClaveUsoCfdi.'\',\''.$data->NombreUsoCfdi.'\',\''.$data->ClavePais.'\',\''.$data->NombrePais.'\')">Seleccionar</div>';
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
            ->leftJoin('c_FormaPago as fp', 'fp.Clave', '=', 'c.FormaPago')
            ->leftJoin('c_MetodoPago as mp', 'mp.Clave', '=', 'c.MetodoPago')
            ->leftJoin('c_UsoCFDI as uc', 'uc.Clave', '=', 'c.UsoCfdi')
            ->leftJoin('c_Pais as p', 'p.Clave', '=', 'c.Pais')
            ->select('c.Numero', 'c.Status', 'fp.Clave AS claveformapago', 'fp.Nombre AS formapago', 'mp.Clave AS clavemetodopago', 'mp.Nombre AS metodopago', 'uc.Clave AS claveusocfdi', 'uc.Nombre AS usocfdi', 'p.Clave AS claveresidenciafiscal', 'p.Nombre AS residenciafiscal')
            ->where('c.Numero', $request->numerocliente)
            ->where('c.Status', 'ALTA')
            ->get();
            $claveformapago = $datos[0]->claveformapago;
            $formapago = $datos[0]->formapago;
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
            $rfc = $cliente->Rfc;
            $facturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->orderBy('Folio', 'DESC')->get();
            $numerofacturas = Factura::where('Cliente', $request->numerocliente)->where('Status', 'POR COBRAR')->count();
            $filasfacturas= '';
            $contadorfilas = 0;
            if($numerofacturas > 0){
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
            $data = FolioComprobantePago::where('Status', 'ALTA')->OrderBy('Numero', 'DESC')->get();
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
        $CuentaXCobrar->Hora=Carbon::parse($request->fecha)->toDateTimeString();
        $CuentaXCobrar->Status="ALTA";
        $CuentaXCobrar->Usuario=Auth::user()->user;
        $CuentaXCobrar->Periodo=$this->periodohoy;
        $CuentaXCobrar->save();
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
        $banco = Banco::where('Numero', $cuentaxcobrar->Banco)->first();
        $filasdetallecuentasporcobrar = '';
        $contadorfilas = 0;
        $contadorproductos = 0;
        $arrayfacturas = array();
        if($numerocuentaxcobrardetalle > 0){
            foreach($cuentaxcobrardetalle as $cxcd){
                array_push($arrayfacturas, $cxcd->Factura);
                $factura = Factura::where('Factura', $cxcd->Factura)->first();
                $filasdetallecuentasporcobrar= $filasdetallecuentasporcobrar.
                '<tr class="filasfacturas" id="filafactura'.$contadorfilas.'">'.
                    '<td class="tdmod"><input type="hidden" class="form-control agregadoen" name="agregadoen[]" value="NA" readonly></td>'.
                    '<td class="tdmod"><input type="hidden" class="form-control facturaaplicarpartida" name="facturaaplicarpartida[]" value="'.$factura->Factura.'" readonly>'.$factura->Factura.'</td>'.
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
            }
        }
        //permitir o no modificar registro
        if(Auth::user()->role_id == 1){
            if($cuentaxcobrar->Status == 'BAJA'){
                $modificacionpermitida = 0;
            }else{
                $modificacionpermitida = 1;
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
                    $modificacionpermitida = 1;
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
            'cliente' => $cliente,
            'regimenfiscal' => $regimenfiscal,
            'tiporelacion' => $tiporelacion,
            'formapago' => $formapago,
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
            'FechaPago' => Carbon::parse($request->fechaaplicacionpagos)->toDateTimeString(),
            'Banco' => $request->numerobanco,
            'Anotacion' => $request->anotacion,
            'Moneda' => $request->moneda,
            'TipoCambio' => $request->pesosmoneda,
            'LugarExpedicion' => $request->lugarexpedicion,
            'RegimenFiscal' => $request->claveregimenfiscal,
            'FormaPago' => $request->claveformapago
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
            $data = CuentaXCobrar::where('Pago', 'like', '%' . $string . '%')->orderBy('Folio', 'ASC')->take(3)->get();
            return DataTables::of($data)
                ->addColumn('operaciones', function($data){
                    $boton =    '<div class="btn bg-amber btn-xs waves-effect" data-toggle="tooltip" title="Agregar para generar PDF" onclick="agregararraypdf(\''.$data->Pago .'\')"><i class="material-icons">done</i></div> ';
                    return $boton;
                })
                ->addColumn('Abono', function($data){
                    return Helpers::convertirvalorcorrecto($data->Abono);
                })
                ->rawColumns(['operaciones','Total'])
                ->make(true);
        } 
    }

    //generar documento PDF
    public function cuentas_por_cobrar_generar_pdfs(Request $request){
        //primero eliminar todos los archivos de la carpeta
        Helpers::eliminararchivospdfsgenerados();
        $tipogeneracionpdf = $request->tipogeneracionpdf;
        if($tipogeneracionpdf == 0){
            $cuentasporcobrar = CuentaXCobrar::whereIn('Pago', $request->arraypdf)->orderBy('Folio', 'ASC')->take(150)->get(); 
        }else{
            $fechainiciopdf = date($request->fechainiciopdf);
            $fechaterminacionpdf = date($request->fechaterminacionpdf);
            $cuentasporcobrar = CuentaXCobrar::whereBetween('Fecha', [$fechainiciopdf, $fechaterminacionpdf])->orderBy('Folio', 'ASC')->take(150)->get();
        }
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        foreach ($cuentasporcobrar as $cxc){
            $data=array();
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
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
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
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
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $request->numerodecimalesdocumento
            );        
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '-1');
            $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
            ->setPaper('Letter')
            ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
            ->setOption('footer-center', 'Página [page] de [toPage]')
            //->setOption('footer-right', ''.$fechaformato.'')
            ->setOption('footer-font-size', 7)
            ->setOption('margin-left', 2)
            ->setOption('margin-right', 2)
            ->setOption('margin-bottom', 10);
            //return $pdf->stream();
            $ArchivoPDF = "PDF".$cxc->Pago.".pdf";
            $pdf->save(storage_path('archivos_pdf_documentos_generados/'.$ArchivoPDF));
        }
        $pdfMerger = PDFMerger::init(); //Initialize the merger
        //unir pdfs
        foreach ($cuentasporcobrar as $cxco){
            $ArchivoPDF = "PDF".$cxco->Pago.".pdf";
            $urlarchivo = storage_path('/archivos_pdf_documentos_generados/'.$ArchivoPDF);
            $pdfMerger->addPDF($urlarchivo, 'all');
        }
        $pdfMerger->merge(); //unirlos
        $pdfMerger->save("CuentasPorCobrar.pdf", "browser");//mostrarlos en el navegador
    }

    //generacion de formato en PDF
    public function cuentas_por_cobrar_generar_pdfs_indiv($documento){
        $cuentasporcobrar = CuentaXCobrar::where('Pago', $documento)->get(); 
        $fechaformato =Helpers::fecha_exacta_accion_datetimestring();
        $data=array();
        foreach ($cuentasporcobrar as $cxc){
            $formatter = new NumeroALetras;
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
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
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
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
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

    //obtener datos para enviar email
    public function cuentas_por_cobrar_obtener_datos_envio_email(Request $request){
        $cuentaporcobrar = CuentaXCobrar::where('Pago', $request->documento)->first();
        $cliente = Cliente::where('Numero',$cuentaporcobrar->Cliente)->first();
        $data = array(
            'cuentaporcobrar' => $cuentaporcobrar,
            'cliente' => $cliente,
            'emailde' => Config::get('mail.from.address'),
            'emailpara' => $cliente->Email1
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
            $abonoletras =  $formatter->toInvoice($cxc->Abono, $this->numerodecimales, 'M.N.');
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
            $comprobantetimbrado = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->count();
            $comprobante = Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $cxc->Folio . '')->where('Serie', '' . $cxc->Serie . '')->first();
            $formatter = new NumeroALetras;
            $totalletras = $formatter->toInvoice($cxc->Abono, 2, 'M.N.');
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
                        "estadocliente" => $estadocliente,
                        "datadetalle" => $datadetalle,
                        "tipocambiocxc"=>Helpers::convertirvalorcorrecto($cxc->TipoCambio),
                        "totalletras"=>$totalletras,
                        "numerodecimalesdocumento"=> $this->numerodecimalesendocumentos
            );
        }
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('registros.cuentasporcobrar.formato_pdf_cuentasporcobrar', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-left', 'Este pago es una representación impresa de un CFDi')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        //->setOption('footer-right', ''.$fechaformato.'')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
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
            //enviar correo electrónico	
            $nombre = 'Receptor envio de correos';
            $receptor = $request->emailpara;
            $correos = [$request->emailpara];
            $asunto = $request->emailasunto;
            $emaildocumento = $request->emaildocumento;
            $name = "Receptor envio de correos";
            $body = $request->emailasunto;
            $horaaccion = Helpers::fecha_exacta_accion_datetimestring();
            $horaaccionespanol = Helpers::fecha_espanol($horaaccion);
            if (file_exists($url_xml) != false) {
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $asunto, $pdf, $emaildocumento,$url_xml) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento,$url_xml)
                            ->cc($correos)
                            ->subject($asunto)
                            ->attachData($pdf->output(), "CuentaPorCobrarNo".$emaildocumento.".pdf")
                            ->attach($url_xml);
                });
                //eliminar xml de storage/xml_cargados
                unlink($url_xml);
            }else{
                Mail::send('correos.enviodocumentosemail.enviodocumentosemail', compact('nombre', 'name', 'body', 'receptor', 'horaaccion', 'horaaccionespanol'), function($message) use ($nombre, $receptor, $correos, $asunto, $pdf, $emaildocumento) {
                    $message->to($receptor, $nombre, $asunto, $pdf, $emaildocumento)
                            ->cc($correos)
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
        return Excel::download(new CuentasPorCobrarExport($this->campos_consulta,$request->periodo), "cuentasporcobrar-".$request->periodo.".xlsx");   
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
        Configuracion_Tabla::where('tabla', 'CuentasPorCobrar')
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
            array_push($arraydet,   array(
                                        "uuid" => $dp->idDocumento, // UUID_de_factura_relacionada
                                        "installment" => Helpers::convertirvalorcorrecto($dp->NumParcialidad),
                                        "last_balance" => Helpers::convertirvalorcorrecto($dp->ImpSaldoAnt),
                                        "amount" => Helpers::convertirvalorcorrecto($dp->Abono),
                                        "currency" => $dp->MonedaDR,
                                        "folio_number" => $dp->Folio,
                                        "series" => $dp->Serie
                                    )
            );
        }        
        //CUENTA POR COBRAR
        $invoice = array(
            "type" => \Facturapi\InvoiceType::PAGO,
            "customer" => array(
                "legal_name" => $cliente->Nombre,
                "tax_id" => $cliente->Rfc
            ),
            "payments" => array(
                array(
                    "payment_form" => $CXC->FormaPago,
                    "currency" => $CXC->Moneda,
                    "exchange" => Helpers::convertirvalorcorrecto($CXC->TipoCambio),
                    "date" => Helpers::formatoinputdatetime($CXC->FechaPago),
                    "related" => $arraydet
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
            $Comprobante->Version = '3.3';
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
        //colocar fecha de cancelacion en tabla comprobante
        $factura = CuentaXCobrar::where('Pago', $request->facturabajatimbre)->first();
        Comprobante::where('Comprobante', 'Pago')->where('Folio', '' . $factura->Folio . '')->where('Serie', '' . $factura->Serie . '')
        ->update([
            'FechaCancelacion' => Helpers::fecha_exacta_accion_datetimestring()
        ]);
        //cancelar timbre facturapi
        $timbrecancelado = $this->facturapi->Invoices->cancel($request->iddocumentofacturapi);
        return response()->json($timbrecancelado);
    }

}
