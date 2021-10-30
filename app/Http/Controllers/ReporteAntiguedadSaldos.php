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
use App\Factura;
use App\FacturaDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\Cliente;
use App\Agente;
use App\Serie;
use App\Producto;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesAntiguedadSaldosExport;
use DB;

class ReporteAntiguedadSaldos extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_antiguedad_saldos(Request $request){
        $urlgenerarformatoexcel = route('reporte_antiguedad_saldos_generar_formato_excel');
        return view('reportes.facturas.reporteantiguedadsaldos', compact('urlgenerarformatoexcel'));
    }
    //obtener clientes
    public function reporte_antiguedad_saldos_obtener_clientes(Request $request){
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
    public function reporte_antiguedad_saldos_obtener_cliente_por_numero(Request $request){
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
    //obtener series
    public function reporte_antiguedad_saldos_obtener_series(Request $request){
        if($request->ajax()){
            $data = Factura::select('Serie')->groupby('Serie')->get();
            return DataTables::of($data)
                    ->addColumn('operaciones', function($data){
                        $boton = '<div class="btn bg-green btn-xs waves-effect" onclick="seleccionarserie(\''.$data->Serie .'\')">Seleccionar</div>';
                        return $boton;
                    })
                    ->rawColumns(['operaciones'])
                    ->make(true);
        }
    }
    //obtener serie por clave
    public function reporte_antiguedad_saldos_obtener_serie_por_clave(Request $request){
        $claveserie = '';
        $existeserie = Factura::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->count();
        if($existeserie > 0){
            $serie = Factura::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->first();
            $claveserie = $serie->Serie;
        }
        $data = array(
            'claveserie' => $claveserie,
        );
        return response()->json($data);
    }
    
    //generar reporte
    public function reporte_antiguedad_saldos_generar_reporte(Request $request){
        $fechacorte = date($request->fechacorte);
        $numerocliente=$request->numerocliente;
        $claveserie=$request->claveserie;
        $departamento=$request->departamento;
        $idsaldomayor=$request->idsaldomayor;
        $reporte = $request->reporte;
        switch($reporte){
            case "GENERAL":

                /*
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'f.Agente', '=', 'a.Numero')
                ->select("f.Factura", "f.Serie", "f.Folio", "f.Depto", "f.Tipo", "f.Cliente", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", "f.Fecha", "f.Plazo", "f.Pedido", "f.Importe", "f.Descuento", "f.SubTotal", "f.Iva", "f.Total", "f.Abonos", "f.Descuentos", "f.Saldo", "f.Costo", "f.Utilidad", "f.Moneda", "f.TipoCambio", "f.Obs", "f.Status", "f.MotivoBaja", "f.Usuario")
                ->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('f.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('f.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.Depto', $departamento);
                    }
                })
                ->where(function($q) use ($documentos) {
                    if($documentos != 'TODOS'){
                        if($documentos == 'FACTURAS'){
                            $q->where('f.Esquema', '<>', 'INTERNA');
                        }
                        if($documentos == 'INTERNOS'){
                            $q->where('f.Esquema', 'INTERNA');
                        }
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        $q->where('f.Status', $status);
                    }
                })
                ->orderby('f.Serie', 'ASC')
                ->orderby('f.Folio', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Abonos', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Abonos), $this->numerodecimales); })
                ->addColumn('Descuentos', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuentos), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
                ->addColumn('MotivoBaja', function($data){ return substr($data->MotivoBaja, 0, 30); })
                ->make(true);
                */
                break;
            case "DETALLES":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'f.Agente', '=', 'a.Numero')
                ->leftjoin('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->select("f.Factura", "f.Fecha", "f.Cliente", "c.Nombre as NombreCliente", "f.Agente", "c.Tipo", "a.Nombre as NombreAgente", "f.Plazo", "fd.Codigo", "fd.Descripcion", "fd.Unidad", "fd.Cantidad", "fd.Precio", "fd.Importe", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Impuesto", "fd.Iva", "fd.Total", "fd.Costo", "fd.CostoTotal", "fd.Utilidad", "fd.Facturar", "fd.Remision", "fd.Orden", "fd.Departamento", "fd.Cargo", "fd.Almacen", "fd.Partida", "fd.Item")
                ->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('f.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('f.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.Depto', $departamento);
                    }
                })
                ->where(function($q) use ($documentos) {
                    if($documentos != 'TODOS'){
                        if($documentos == 'FACTURAS'){
                            $q->where('f.Esquema', '<>', 'INTERNA');
                        }
                        if($documentos == 'INTERNOS'){
                            $q->where('f.Esquema', 'INTERNA');
                        }
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        $q->where('f.Status', $status);
                    }
                })
                ->orderby('f.Factura', 'ASC')
                ->orderby('f.Fecha', 'ASC')
                ->orderby('fd.Item', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Descripcion', function($data) { return substr($data->Descripcion, 0 ,30); })
                ->addColumn('Cantidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Cantidad), $this->numerodecimales); })
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Dcto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Dcto), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Impuesto', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Impuesto), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('CostoTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->make(true);
                break;
        }
    }
    //generar reporte en excel
    public function reporte_antiguedad_saldos_generar_formato_excel(Request $request){
        return Excel::download(new ReportesAntiguedadSaldosExport($request->fechacorte, $request->numerocliente, $request->claveserie, $request->departamento, $request->idsaldomayor, $request->reporte, $this->numerodecimales, $this->empresa), "formatoantiguedadsaldos-".$request->reporte.".xlsx"); 
    }


}
