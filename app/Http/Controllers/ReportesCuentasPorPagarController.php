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
use App\ContraRecibo;
use App\ContraReciboDetalle;
use App\Proveedor;
use App\Banco;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionCuentasPorPagarExport;
use DB;
use PDF;

class ReportesCuentasPorPagarController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_cuentasporpagar(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_cuentasporpagar_generar_formato_excel');
        $urlgenerarformatopdf = route('reporte_relacion_cuentasporpagar_generar_formato_pdf');
        return view('reportes.cuentasporpagar.reporterelacioncuentasporpagar', compact('urlgenerarformatoexcel','urlgenerarformatopdf'));
    }
    //obtener proveedores
    public function reporte_relacion_cuentasporpagar_obtener_proveedores(Request $request){
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
    //obtener proveedor por numero
    public function reporte_relacion_cuentasporpagar_obtener_proveedor_por_numero(Request $request){
        $numero = '';
        $nombre = '';
        $existeproveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->count();
        if($existeproveedor > 0){
            $proveedor = Proveedor::where('Numero', $request->numeroproveedor)->where('Status', 'ALTA')->first();
            $numero = $proveedor->Numero;
            $nombre = $proveedor->Nombre;
        }
        $data = array(
            'numero' => $numero,
            'nombre' => $nombre,
        );
        return response()->json($data);  
    }
    
    //obtener bancos
    public function reporte_relacion_cuentasporpagar_obtener_bancos(Request $request){
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
    public function reporte_relacion_cuentasporpagar_obtener_banco_por_numero(Request $request){
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
    public function reporte_relacion_cuentasporpagar_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numeroproveedor=$request->numeroproveedor;
        $numerobanco=$request->numerobanco;
        switch($reporte){
            case "AGRUPARxPROVEEDOR":
                $data = DB::table('CxP as cxp')
                ->leftjoin('CxP Detalles as cxpd', 'cxp.Pago', '=', 'cxpd.Pago')
                ->leftjoin('Compras as c', 'cxpd.Compra', '=', 'c.Compra')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxpd.Pago', 'cxpd.Fecha', 'p.Numero', 'p.Nombre AS Proveedor', 'c.Saldo', 'b.Nombre as Banco', 'cxpd.Compra', 'c.Remision', 'c.Factura', 'c.Total', 'cxpd.Abono', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status', 'cxpd.Item')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('cxp.Serie', 'DESC')
                ->orderby('cxp.Folio', 'DESC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Proveedor', function($data){ return substr($data->Proveedor, 0, 40); })
                ->addColumn('Beneficiario', function($data){ return substr($data->Beneficiario, 0, 40); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 40); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->make(true);
                break;
            case "AGRUPARxBANCO":
                break;
            case "RELACIONPAGOS":
                $data = DB::table('CxP as cxp')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxp.Pago', 'cxp.Fecha', 'p.Numero', 'p.Nombre AS Proveedor', 'b.Nombre as Banco', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Abono', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('cxp.Serie', 'DESC')
                ->orderby('cxp.Folio', 'DESC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Proveedor', function($data){ return substr($data->Proveedor, 0, 40); })
                ->addColumn('Beneficiario', function($data){ return substr($data->Beneficiario, 0, 40); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 40); })
                ->addColumn('Abono', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abono), $this->numerodecimales); })
                ->make(true);
                break;
        }
    }
    //generar excel reporte relacion ordenes compra
    public function reporte_relacion_cuentasporpagar_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionCuentasPorPagarExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numeroproveedor, $request->numerobanco, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacioncuentasporpagar-".$request->reporte.".xlsx"); 
    }
    //generar reporte en pdf
    public function reporte_relacion_cuentasporpagar_generar_formato_pdf(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numeroproveedor=$request->numeroproveedor;
        $numerobanco=$request->numerobanco;
        switch($reporte){
            case "AGRUPARxPROVEEDOR":
                $consultarep = DB::table('CxP as cxp')
                ->leftjoin('CxP Detalles as cxpd', 'cxp.Pago', '=', 'cxpd.Pago')
                ->leftjoin('Compras as c', 'cxpd.Compra', '=', 'c.Compra')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxpd.Pago', DB::raw("FORMAT(cxpd.Fecha, 'yyyy-MM-dd') as Fecha"), 'p.Numero', 'p.Nombre AS Proveedor', 'p.Saldo as SaldoProveedor', 'c.Saldo', 'b.Nombre as Banco', 'cxpd.Compra', 'c.Remision', 'c.Factura', 'c.Total', 'cxpd.Abono', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status', 'cxpd.Item')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('p.Nombre')
                ->get();
                break;
            case "AGRUPARxBANCO":
                break;
            case "RELACIONPAGOS":
                $consultarep = DB::table('CxP as cxp')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxp.Pago', DB::raw("FORMAT(cxp.Fecha, 'yyyy-MM-dd') as Fecha"), 'p.Numero', 'p.Nombre AS Proveedor', 'b.Nombre as Banco', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Abono', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('cxp.Serie', 'DESC')
                ->orderby('cxp.Folio', 'DESC')
                ->get();
        }
        $data = array(
            'fechainicio' => $fechainicio,
            'fechaterminacion' => $fechaterminacion,
            'reporte' => $reporte,
            'numeroproveedor' => $numeroproveedor,
            'numerobanco' => $numerobanco,
            'numerodecimales' => $this->numerodecimales, 
            'empresa' => $this->empresa,
            'consultarep' => $consultarep
        );
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '-1');
        $pdf = PDF::loadView('reportes.cuentasporpagar.formato_pdf_reporterelacioncuentasporpagar', compact('data'))
        ->setPaper('Letter')
        ->setOption('footer-center', 'Página [page] de [toPage]')
        ->setOption('footer-font-size', 7)
        ->setOption('margin-left', 2)
        ->setOption('margin-right', 2)
        ->setOption('margin-bottom', 10);
        return $pdf->stream();
    }

}
