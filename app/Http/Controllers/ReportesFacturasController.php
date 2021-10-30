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
use App\Exports\ReportesFacturasVentasClientesExport;
use DB;

class ReportesFacturasController extends ConfiguracionSistemaController{

    public function __construct(){
        parent::__construct(); //carga las configuraciones del controlador ConfiguracionSistemaController
    }

    //vista
    public function reporte_facturas_ventas_cliente(Request $request){
        $urlgenerarformatoexcel = route('reporte_facturas_ventas_cliente_generar_formato_excel');
        return view('reportes.facturas.reportefacturasventascliente', compact('urlgenerarformatoexcel'));
    }

    //obtener tipos ordenes de compra
    public function reporte_facturas_ventas_cliente_obtener_tipos_ordenes_compra(Request $request){
        $tipos_ordenes_compra = TipoOrdenCompra::where('STATUS', 'ALTA')->get();
        $select_tipos_ordenes_compra = "<option value='TODOS' selected>TODOS</option>";
        foreach($tipos_ordenes_compra as $tipo){
            $select_tipos_ordenes_compra = $select_tipos_ordenes_compra."<option value='".$tipo->Nombre."'>".$tipo->Nombre."</option>";
        }
        return response()->json($select_tipos_ordenes_compra);
    }
    //obtener clientes
    public function reporte_facturas_ventas_cliente_obtener_clientes(Request $request){
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
    public function reporte_facturas_ventas_cliente_obtener_cliente_por_numero(Request $request){
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
    public function reporte_facturas_ventas_cliente_obtener_agentes(Request $request){
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
    public function reporte_facturas_ventas_cliente_obtener_agente_por_numero(Request $request){
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
    public function reporte_facturas_ventas_cliente_obtener_series(Request $request){
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
    public function reporte_facturas_ventas_cliente_obtener_serie_por_clave(Request $request){
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
    public function reporte_facturas_ventas_cliente_generar_reporte(Request $request){
        $fechainicio = date($request->fechainicialreporte);
        $fechaterminacion = date($request->fechafinalreporte);
        $numerocliente=$request->numerocliente;
        $numeroagente=$request->numeroagente;
        $claveserie=$request->claveserie;
        $tipo=$request->tipo;
        $departamento=$request->departamento;
        $documentos=$request->documentos;
        $status=$request->status;
        $reporte = $request->reporte;
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
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
                break;
            case "PRODUCTOS":
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
            case "VENTAS":
                break;
            case "PAGOS":
                break;
            case "FACTURAS":
                break;
            case "RESUMEN":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->select('f.Cliente', 'c.Nombre', DB::raw("SUM(f.Importe) as Importe"), DB::raw("SUM(f.Descuento) as Descuento"), DB::raw("SUM(f.SubTotal) as SubTotal"), DB::raw("SUM(f.Iva) as Iva"), DB::raw("SUM(f.Total) as Total"), DB::raw("SUM(f.Costo) as Costo"), DB::raw("SUM(f.Utilidad) as Utilidad"), DB::raw("case sum(f.SubTotal) when 0 then 0 else sum(f.Utilidad)*100/sum(f.SubTotal) end as PorcentajeUtilidad"))
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
                ->groupby('f.Cliente')
                ->groupby('c.Nombre')
                ->orderby('f.Cliente', 'ASC')
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
                                'SubTotal' => Factura::select(DB::raw("SUM(SubTotal)"))->whereColumn('Cliente', 'c.Numero')
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
                                                        ->where(function($q) use ($departamento) {
                                                            if($departamento != 'TODOS'){
                                                                $q->where('Depto', $departamento);
                                                            }
                                                        })
                                                        ->where(function($q) use ($documentos) {
                                                            if($documentos != 'TODOS'){
                                                                if($documentos == 'FACTURAS'){
                                                                    $q->where('Esquema', '<>', 'INTERNA');
                                                                }
                                                                if($documentos == 'INTERNOS'){
                                                                    $q->where('Esquema', 'INTERNA');
                                                                }
                                                            }
                                                        })
                                                        ->where(function($q) use ($tipo) {
                                                            if($tipo != 'TODOS'){
                                                                $q->where('Tipo', $tipo);
                                                            }
                                                        })
                                                        ->where(function($q) use ($status) {
                                                            if($status != 'TODOS'){
                                                                $q->where('Status', $status);
                                                            }
                                                        })
														->limit(1)
                            ])
							->addselect([
                                'Utilidad' => Factura::select(DB::raw("CASE
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
                                                        ->where(function($q) use ($departamento) {
                                                            if($departamento != 'TODOS'){
                                                                $q->where('Depto', $departamento);
                                                            }
                                                        })
                                                        ->where(function($q) use ($documentos) {
                                                            if($documentos != 'TODOS'){
                                                                if($documentos == 'FACTURAS'){
                                                                    $q->where('Esquema', '<>', 'INTERNA');
                                                                }
                                                                if($documentos == 'INTERNOS'){
                                                                    $q->where('Esquema', 'INTERNA');
                                                                }
                                                            }
                                                        })
                                                        ->where(function($q) use ($tipo) {
                                                            if($tipo != 'TODOS'){
                                                                $q->where('Tipo', $tipo);
                                                            }
                                                        })
                                                        ->where(function($q) use ($status) {
                                                            if($status != 'TODOS'){
                                                                $q->where('Status', $status);
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
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->select('c.Numero', 'c.Nombre', 'f.Plazo', 'c.Credito', 'c.Bloquear', 'c.Saldo', DB::raw("SUM(f.Total) as TotalFacturas"))
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
                ->where('f.Status', '<>', 'BAJA')            
                ->groupby('c.Numero', 'c.Nombre', 'f.Plazo', 'c.Credito', 'c.Bloquear', 'c.Saldo')
                ->orderby(DB::raw("SUM(f.Total)"), 'DESC')
                ->get();
                return DataTables::of($data)
                ->addColumn('Nombre', function($data){ return substr($data->Nombre, 0, 30); })
                ->addColumn('Credito', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Credito), $this->numerodecimales); })
                ->addColumn('Saldo', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Saldo), $this->numerodecimales); })
                ->addColumn('TotalFacturas', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->TotalFacturas), $this->numerodecimales); })
                ->make(true);
                break;
            case "COMPARATIVO MENSUAL":
                //filtros en consulta
                $wheres = "";
                if($numerocliente != ""){
                    $wheres = $wheres . "and f.Cliente = ".$numerocliente." ";
                }
                if($numeroagente != ""){
                    $wheres = $wheres . "and f.Agente = ".$numeroagente." ";

                }
                if($claveserie != ""){
                    $wheres = $wheres . "and f.Serie = '".$claveserie."' ";
                }
                if($departamento != 'TODOS'){
                    $wheres = $wheres . "and f.Depto = '".$departamento."' ";
                }
                if($documentos != 'TODOS'){
                    if($documentos == 'FACTURAS'){
                        $wheres = $wheres . "and f.Esquema <> 'INTERNA' ";
                    }
                    if($documentos == 'INTERNOS'){
                        $wheres = $wheres . "and f.Esquema = 'INTERNA' ";
                    }
                }
                if($tipo != 'TODOS'){
                    $wheres = $wheres . "and f.Tipo = '".$tipo."' ";
                }
                if($status != 'TODOS'){
                    $wheres = $wheres . "and f.Status = '".$status."' ";
                }
                //obtener todos los meses y años entre la fecha inicial y final del reporte
                $todaslasfechas = array();
                for($i=$fechainicio;$i<=$fechaterminacion;$i = date("Y-m-d", strtotime($i ."+ 1 days"))){
                    if (in_array(date("Y-m", strtotime($i)), $todaslasfechas)) {   
                    }else{
                        array_push($todaslasfechas, date("Y-m", strtotime($i)));
                    }                
                }
                //armar los joins con base a los meses y años obtenidos 
                $joins = "";
                $dinamycselects = "";
                foreach($todaslasfechas as $joinselect){
                    $mesyano = explode("-", $joinselect);
                    $ano = $mesyano[0];
                    $mes = $mesyano[1];
                    $joins = $joins . "left join (select f.cliente, sum(f.subtotal) as [SubTotal".$joinselect."], sum(f.utilidad) as [Utilidad".$joinselect."], case when sum(f.subtotal) <= 0 then 0 else 100*sum(f.utilidad)/sum(f.subtotal) end as [PorcentajeUtilidad".$joinselect."] from facturas f where year(f.fecha) = ".$ano." and month(f.fecha) = ".$mes." ".$wheres." group by f.cliente) AS t".$ano.$mes." on l.numero = t".$ano.$mes.".cliente ";
                    $dinamycselects = $dinamycselects."[SubTotal".$joinselect."], [Utilidad".$joinselect."], [PorcentajeUtilidad".$joinselect."], ";
                }
                $dinamyc = rtrim($dinamycselects, ", ");
                $select = "select l.Numero AS Cliente, l.Nombre AS NombreCliente, ".$dinamyc." from clientes l ";
                $data = DB::select($select. $joins. "order by l.numero");
                return DataTables::of($data)
                ->make(true);
                break;
            case "COMPARATIVO ANUAL":
                //filtros en consulta
                $wheres = "";
                if($numerocliente != ""){
                    $wheres = $wheres . "and f.Cliente = ".$numerocliente." ";
                }
                if($numeroagente != ""){
                    $wheres = $wheres . "and f.Agente = ".$numeroagente." ";

                }
                if($claveserie != ""){
                    $wheres = $wheres . "and f.Serie = '".$claveserie."' ";
                }
                if($departamento != 'TODOS'){
                    $wheres = $wheres . "and f.Depto = '".$departamento."' ";
                }
                if($documentos != 'TODOS'){
                    if($documentos == 'FACTURAS'){
                        $wheres = $wheres . "and f.Esquema <> 'INTERNA' ";
                    }
                    if($documentos == 'INTERNOS'){
                        $wheres = $wheres . "and f.Esquema = 'INTERNA' ";
                    }
                }
                if($tipo != 'TODOS'){
                    $wheres = $wheres . "and f.Tipo = '".$tipo."' ";
                }
                if($status != 'TODOS'){
                    $wheres = $wheres . "and f.Status = '".$status."' ";
                }
                //obtener todos los meses y años entre la fecha inicial y final del reporte
                $todaslasfechas = array();
                for($i=$fechainicio;$i<=$fechaterminacion;$i = date("Y-m-d", strtotime($i ."+ 1 days"))){
                    if (in_array(date("Y", strtotime($i)), $todaslasfechas)) {   
                    }else{
                        array_push($todaslasfechas, date("Y", strtotime($i)));
                    }                
                }
                //armar los joins con base a los meses y años obtenidos 
                $joins = "";
                $dinamycselects = "";
                foreach($todaslasfechas as $joinselect){
                    $joins = $joins . "left join (select f.cliente, sum(f.total) as [Facturado".$joinselect."] from facturas f where f.periodo = ".$joinselect." ".$wheres." group by f.cliente) t".$joinselect." on l.numero = t".$joinselect.".cliente ";
                    $dinamycselects = $dinamycselects."[Facturado".$joinselect."], ";
                }
                $dinamyc = rtrim($dinamycselects, ", ");
                $select = "select l.Numero AS Cliente, l.Nombre AS NombreCliente, ".$dinamyc." from clientes l ";
                $data = DB::select($select. $joins. "order by l.numero");
                return DataTables::of($data)
                ->make(true);
                break;
            case "NOTAS DE CREDITO":
                $facturas = DB::table('Facturas')
                                ->select(DB::raw("'Ingreso' as Comprobante"), 'Factura as Documento', 'Fecha', 'SubTotal', 'Iva', 'Total')
                                ->whereBetween('Fecha', [$fechainicio, $fechaterminacion]);
                $data = DB::table('Notas Cliente')
                            ->select(DB::raw("'Egreso' as Comprobante"), 'Nota as Documento', 'Fecha', DB::raw("-SubTotal AS SubTotal"), DB::raw("-Iva AS Iva"), DB::raw("-Total AS Total"))
                            ->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
                            ->union($facturas)
                            ->orderby('Fecha', 'ASC')
                            ->get();
                return DataTables::of($data)
                ->addColumn('SubTotal', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->SubTotal), $this->numerodecimales); })
                ->addColumn('Iva', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Iva), $this->numerodecimales); })
                ->addColumn('Total', function($data){ return number_format(Helpers::convertirvalorcorrecto($data->Total), $this->numerodecimales); })
                ->make(true);
                break;
            case "NO FACTURADOS":
                break;
        }
    }
    //generar reporte en excel
    public function reporte_facturas_ventas_cliente_generar_formato_excel(Request $request){
        return Excel::download(new ReportesFacturasVentasClientesExport($request->fechainicialreporte, $request->fechafinalreporte, $request->numerocliente, $request->numeroagente, $request->claveserie, $request->tipo, $request->departamento, $request->documentos, $request->status, $request->reporte, $this->numerodecimales, $this->empresa), "formatorelacionventasclientes-".$request->reporte.".xlsx"); 
    }
    
}
