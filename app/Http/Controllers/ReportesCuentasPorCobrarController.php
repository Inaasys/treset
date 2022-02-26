<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use DataTables;
use App\Configuracion_Tabla;
use App\Remision;
use App\RemisionDetalle;
use App\Cliente;
use App\Agente;
use App\Banco;
use App\FormaPago;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionCuentasPorCobrarExport;
use DB;
use PDF;

class ReportesCuentasPorCobrarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_cuentasporcobrar(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_cuentasporcobrar_generar_formato_excel');
        $urlgenerarformatopdf = route('reporte_relacion_cuentasporcobrar_generar_formato_pdf');
        return view('reportes.cuentasporcobrar.reporterelacioncuentasporcobrar', compact('urlgenerarformatoexcel','urlgenerarformatopdf'));
    }

    
    //obtener clientes
    public function reporte_relacion_cuentasporcobrar_obtener_clientes(Request $request){
        if($request->ajax()){
            $data = Cliente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarcliente('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener clientes por numero
    public function reporte_relacion_cuentasporcobrar_obtener_cliente_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existecliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->count();
        if($existecliente > 0){
            $cliente = Cliente::where('Numero', $request->numerocliente)->where('Status', 'ALTA')->first();
            $numero = $cliente->Numero;
            $nombre = $cliente->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);
    }
    //obtener agentes
    public function reporte_relacion_cuentasporcobrar_obtener_agentes(Request $request){
        if($request->ajax()){
            $data = Agente::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionaragente('.$data->Numero.',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener agente por numero
    public function reporte_relacion_cuentasporcobrar_obtener_agente_por_numero(Request $request){
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
            'nombre' => $nombre,
        );
        return response()->json($data);
    }
    //obtener formas pagos
    public function reporte_relacion_cuentasporcobrar_obtener_formaspago(Request $request){
        if($request->ajax()){
            $data = FormaPago::orderBy("Clave", "ASC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarformapago(\''.$data->Clave .'\',\''.$data->Nombre.'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener forma pago por clave
    public function reporte_relacion_cuentasporcobrar_obtener_formapago_por_clave(Request $request){
        $clave = '';
        $nombre = '';
        $existeformapago = FormaPago::where('Clave', $request->claveformapago)->count();
        if($existeformapago > 0){
            $formapago = FormaPago::where('Clave', $request->claveformapago)->first();
            $clave = $formapago->Clave;
            $nombre = $formapago->Nombre;
        }
        $data = array(
            'clave' => $clave,
            'nombre' => $nombre,
        );
        return response()->json($data);
    } 
    //obtener bancos
    public function reporte_relacion_cuentasporcobrar_obtener_bancos(Request $request){
        if($request->ajax()){
            $data = Banco::where('Status', 'ALTA')->orderBy("Numero", "DESC")->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarbanco('.$data->Numero.',\''.$data->Nombre .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener proveedor por numero
    public function reporte_relacion_cuentasporcobrar_obtener_banco_por_numero(Request $request){
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

    
    //generar reporte
    public function reporte_relacion_cuentasporcobrar_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $numerobanco=$request->numerobanco;
        $claveformapago=$request->claveformapago;
        switch($reporte){
            case "AGRUPARxCLIENTES":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                return DataTables::of($data)
                ->addColumn('Cliente', function($data){ return substr($data->Cliente, 0, 30); })
                ->addColumn('FormaPago', function($data){ return substr($data->FormaPago, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                break;
            case "AGRUPARxAGENTES":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                return DataTables::of($data)
                ->addColumn('Cliente', function($data){ return substr($data->Cliente, 0, 30); })
                ->addColumn('FormaPago', function($data){ return substr($data->FormaPago, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                break;
            case "AGRUPARxFORMADEPAGO":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                return DataTables::of($data)
                ->addColumn('Cliente', function($data){ return substr($data->Cliente, 0, 30); })
                ->addColumn('FormaPago', function($data){ return substr($data->FormaPago, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                break;
            case "AGRUPARxBANCO":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                return DataTables::of($data)
                ->addColumn('Cliente', function($data){ return substr($data->Cliente, 0, 30); })
                ->addColumn('FormaPago', function($data){ return substr($data->FormaPago, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                break;
            case "RELACIONDEPAGOS":
                $data = DB::table('CxC as cxc')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxc.Pago', 'cxc.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxc.Abono", 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                return DataTables::of($data)
                ->addColumn('Cliente', function($data){ return substr($data->Cliente, 0, 30); })
                ->addColumn('FormaPago', function($data){ return substr($data->FormaPago, 0, 30); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                break;
            case "COMISIONAAGENTES":
                break;
        }
    }
    //generar reporte en excel
    public function reporte_relacion_cuentasporcobrar_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionCuentasPorCobrarExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->numeroagente, $request->numerobanco, $request->claveformapago, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacioncuentasporcobrar-".$request->reporte.".xlsx"); 
    }
    //generar formto pdf
    public function reporte_relacion_cuentasporcobrar_generar_formato_pdf(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $numerobanco=$request->numerobanco;
        $claveformapago=$request->claveformapago;
        switch($reporte){
            case "AGRUPARxCLIENTES":
                $consultarep = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Numero as NumeroCliente', 'c.Nombre as Cliente', 'c.Saldo as SaldoCliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('c.Numero')
                ->get();
                break;
            case "AGRUPARxAGENTES":
                $consultarep = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "AGRUPARxFORMADEPAGO":
                $consultarep = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "AGRUPARxBANCO":
                $consultarep = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "RELACIONDEPAGOS":
                $consultarep = DB::table('CxC as cxc')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxc.Pago', 'cxc.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxc.Abono", 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "COMISIONAAGENTES":
                break;
        }
        
        $data = array(
            'fechainicio' => $fechainicio,
            'fechaterminacion' => $fechaterminacion,
            'reporte' => $reporte,
            'numerocliente' => $numerocliente,
            'numerobanco' => $numerobanco,
            'numeroagente' => $numeroagente,
            'claveformapago' => $claveformapago,
            'numerodecimales' => $this->numerodecimales, 
            'empresa' => $this->empresa,
            'consultarep' => $consultarep
        );
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('reportes.cuentasporcobrar.formato_pdf_reporterelacioncuentasporcobrar', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

}
