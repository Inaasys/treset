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
use App\Serie;
use App\FormaPago;
use App\TipoOrdenCompra;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesRelacionRemisionesExport;
use DB;

class ReportesRemisionesController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_relacion_remisiones(Request $request){
        $urlgenerarformatoexcel = route('reporte_relacion_remisiones_generar_formato_excel');
        return view('reportes.remisiones.reporterelacionremisiones', compact('urlgenerarformatoexcel'));
    }
    //obtener tipos ordenes de compra
    public function reporte_relacion_remisiones_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener clientes
    public function reporte_relacion_remisiones_obtener_clientes(Request $request){
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
    public function reporte_relacion_remisiones_obtener_cliente_por_numero(Request $request){
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
    public function reporte_relacion_remisiones_obtener_agentes(Request $request){
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
    public function reporte_relacion_remisiones_obtener_agente_por_numero(Request $request){
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
    //obtener series
    public function reporte_relacion_remisiones_obtener_series(Request $request){
        if($request->ajax()){
            $data = Remision::select('Serie')->groupby('Serie')->get();
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
    public function reporte_relacion_remisiones_obtener_serie_por_clave(Request $request){
        $claveserie = '';
        $existeserie = Remision::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->count();

        if($existeserie > 0){
            $serie = Remision::select('Serie')->where('Serie', $request->claveserie)->groupby('Serie')->first();
            $claveserie = $serie->Serie;
        }
        $data = array(
            'claveserie' => $claveserie,
        );
        return response()->json($data);
    }
    //obtener formas pagos
    public function reporte_relacion_remisiones_obtener_formaspago(Request $request){
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
    public function reporte_relacion_remisiones_obtener_formapago_por_clave(Request $request){
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
    //generar reporte
    public function reporte_relacion_remisiones_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $reporte = $request->reporte;
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $claveserie=$request->claveserie;
        $claveformapago=$request->claveformapago;
        $tipo=$request->tipo;
        $status=$request->status;
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
                $data = DB::table('Remisiones as r')
                ->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('r.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('r.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('r.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('r.FormaPago', $claveformapago);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('r.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        if($status == 'FACTURADOS'){
                            $q->where('r.Status', 'like', '%-%');
                        }else{
                            $q->where('r.Status', $status);
                        }
                    }
                })
                ->orderby('r.Serie', 'ASC')
                ->orderby('r.Folio', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Comision', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Comision), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('Obs', function($data){ return substr($data->Obs, 0, 30); })
                ->addColumn('Corte', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Corte), $this->numerodecimales); })
                ->addColumn('SuPago', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SuPago), $this->numerodecimales); })
                ->addColumn('EnEfectivo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->EnEfectivo), $this->numerodecimales); })
                ->addColumn('EnTarjetas', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->EnTarjetas), $this->numerodecimales); })
                ->addColumn('EnVales', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->EnVales), $this->numerodecimales); })
                ->addColumn('EnCheque', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->EnCheque), $this->numerodecimales); })
                ->make(true);
                break;
            case "PRODUCTOS":
                $data = DB::table('Remisiones as r')
                ->leftjoin('Clientes as c', 'r.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'r.Agente', '=', 'a.Numero')
                ->leftjoin('Remisiones Detalles as rd', 'r.Remision', '=', 'rd.Remision')
                ->select('r.Remision', 'r.Fecha', 'r.Cliente', 'c.Nombre AS NombreCliente', 'r.Agente', "a.Nombre AS NombreAgente", 'r.Plazo', 'rd.Codigo', 'rd.Descripcion', 'rd.Unidad', 'rd.Cantidad', 'rd.Precio', 'rd.Importe', 'rd.Dcto AS Dcto %', 'rd.Descuento', 'rd.SubTotal', 'rd.Impuesto', 'rd.Iva', 'rd.Total', 'rd.Costo', 'rd.CostoTotal', 'rd.Utilidad', 'rd.Item')
                ->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('r.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('r.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('r.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('r.FormaPago', $claveformapago);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('r.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        if($status == 'FACTURADOS'){
                            $q->where('r.Status', 'like', '%-%');
                        }else{
                            $q->where('r.Status', $status);
                        }
                    }
                })
                ->orderby('r.Remision', 'ASC')
                ->orderby('r.Fecha', 'ASC')
                ->orderby('rd.Item', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('NombreCliente', function($data){ return substr($data->NombreCliente, 0, 30); })
                ->addColumn('NombreAgente', function($data){ return substr($data->NombreAgente, 0, 30); })
                ->addColumn('Descripcion', function($data){ return substr($data->Descripcion, 0, 30); })
                ->addColumn('Precio', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Precio), $this->numerodecimales); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('CostoTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->CostoTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->make(true);
                break;
            case "VENTAS":
                break;
            case "PAGOS":
                break;
            case "REMISIONES":
                break;
            case "RESUMEN":
                $data = DB::table('Remisiones as r')
                ->leftjoin('Clientes as c', 'r.Cliente', '=', 'c.Numero')
                ->select('r.Cliente', 'c.Nombre', DB::raw("SUM(r.Importe) as Importe"), DB::raw("SUM(r.Descuento) as Descuento"), DB::raw("SUM(r.SubTotal) as SubTotal"), DB::raw("SUM(r.Iva) as Iva"), DB::raw("SUM(r.Total) as Total"), DB::raw("SUM(r.Costo) as Costo"), DB::raw("SUM(r.Utilidad) as Utilidad"), DB::raw("case sum(r.SubTotal) when 0 then 0 else sum(r.Utilidad)*100/sum(r.SubTotal) end as PorcentajeUtilidad"))
                ->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('r.Cliente', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('r.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('r.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('r.FormaPago', $claveformapago);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('r.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        if($status == 'FACTURADOS'){
                            $q->where('r.Status', 'like', '%-%');
                        }else{
                            $q->where('r.Status', $status);
                        }
                    }
                })
                ->groupby('r.Cliente')
                ->groupby('c.Nombre')
                ->orderby('r.Cliente', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
                ->addColumn('Importe', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Importe), $this->numerodecimales); })
                ->addColumn('Descuento', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Descuento), $this->numerodecimales); })
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->addColumn('Costo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Costo), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Utilidad), $this->numerodecimales); })
                ->addColumn('PorcentajeUtilidad', function($data){ return Helpers::convertirvalorcorrecto($data->PorcentajeUtilidad); })
                ->make(true);
                break;
            case "MENSUAL":
                $data = DB::table("Clientes as c")
                ->select('c.Numero AS Cliente', 'c.Nombre AS NombreCliente')
                            ->addselect([
                                'SubTotal' => Remision::select(DB::raw("SUM(SubTotal)"))->whereColumn('Cliente', 'c.Numero')
														->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
														->where(function($q) use ($numerocliente) {
															if($numerocliente != ""){
																$q->where('Cliente', $numerocliente);
															}
														})
														->where(function($q) use ($numeroagente) {
															if($numeroagente != ""){
																$q->where('Agente', $numeroagente);
															}
														})
														->where(function($q) use ($claveserie) {
															if($claveserie != ""){
																$q->where('Serie', $claveserie);
															}
														})
														->where(function($q) use ($claveformapago) {
															if($claveformapago != ""){
																$q->where('FormaPago', $claveformapago);
															}
														})
														->where(function($q) use ($tipo) {
															if($tipo != 'TODOS'){
																$q->where('Tipo', $tipo);
															}
														})
														->where(function($q) use ($status) {
															if($status != 'TODOS'){
																if($status == 'FACTURADOS'){
																	$q->where('Status', 'like', '%-%');
																}else{
																	$q->where('Status', $status);
																}
															}
														})
														->limit(1)
                            ])
							->addselect([
                                'Utilidad' => Remision::select(DB::raw("CASE
																		WHEN SUM(SubTotal) = 0 THEN 0 ELSE
																		SUM(Utilidad)*100/SUM(SubTotal)
																		END"))->whereColumn('Cliente', 'c.Numero')
														->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
														->where(function($q) use ($numerocliente) {
															if($numerocliente != ""){
																$q->where('Cliente', $numerocliente);
															}
														})
														->where(function($q) use ($numeroagente) {
															if($numeroagente != ""){
																$q->where('Agente', $numeroagente);
															}
														})
														->where(function($q) use ($claveserie) {
															if($claveserie != ""){
																$q->where('Serie', $claveserie);
															}
														})
														->where(function($q) use ($claveformapago) {
															if($claveformapago != ""){
																$q->where('FormaPago', $claveformapago);
															}
														})
														->where(function($q) use ($tipo) {
															if($tipo != 'TODOS'){
																$q->where('Tipo', $tipo);
															}
														})
														->where(function($q) use ($status) {
															if($status != 'TODOS'){
																if($status == 'FACTURADOS'){
																	$q->where('Status', 'like', '%-%');
																}else{
																	$q->where('Status', $status);
																}
															}
														})
														->limit(1)
                            ])
                ->orderby('c.Numero', 'ASC')
                ->get();
                return DataTables::of($data)
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Utilidad', function($data){ return Helpers::convertirvalorcorrecto($data->Utilidad); })
                ->make(true);
                break;
            case "POTENCIALES":
                $data = DB::table('Remisiones as r')
                ->leftjoin('Clientes as c', 'r.Cliente', '=', 'c.Numero')
                ->select('c.Numero', 'c.Nombre', 'r.Plazo', DB::raw("SUM(r.Total) as TotalRemisiones"))
                ->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('r.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->where('r.Serie', $claveserie);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('r.FormaPago', $claveformapago);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('r.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        if($status == 'FACTURADOS'){
                            $q->where('r.Status', 'like', '%-%');
                        }else{
                            $q->where('r.Status', $status);
                        }
                    }
                })
                ->where('c.Status', '<>', 'BAJA')            
                ->groupby('c.Numero', 'c.Nombre', 'r.Plazo')
                ->orderby(DB::raw("SUM(r.Total)"), 'DESC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
                ->addColumn('TotalRemisiones', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalRemisiones), $this->numerodecimales); })
                ->make(true);
                break;
            case "CORTE":
                break;
        }
    }
    //generar reporte en excel
    public function reporte_relacion_remisiones_generar_formato_excel(Request $request){
        return Excel::download(new ReportesRelacionRemisionesExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->numeroagente, $request->claveserie, $request->claveformapago, $request->tipo, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacionremisiones-".$request->reporte.".xlsx"); 
    }

}
