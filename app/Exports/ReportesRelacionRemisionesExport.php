<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\Remision;
use App\RemisionDetalle;
use App\Cliente;
use App\Agente;
use DB;

class ReportesRelacionRemisionesExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numerocliente;
    private $numeroagente;
    private $claveserie;
    private $claveformapago;
    private $tipo;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $numeroagente, $claveserie, $claveformapago, $tipo, $status, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
                $this->campos_consulta = array("Remision", "Serie", "Folio", "Cliente", "Agente", "Fecha", "Plazo", "Tipo", "Unidad", "Pedido", "Solicita", "Referencia", "Destino", "Almacen", "TeleMarketing", "Os", "Eq", "Rq", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Costo", "Comision", "Utilidad", "FormaPago", "Obs", "TipoCambio", "Hora", "Facturada", "Corte", "SuPago", "EnEfectivo", "EnTarjetas", "EnVales", "EnCheque", "Lugar", "Personas", "Status", "MotivoBaja", "Equipo", "Usuario", "Periodo");
                break;
            case "PRODUCTOS":
                $this->campos_consulta = array("Remision", "Fecha", "Cliente", "NombreCliente", "Agente", "NombreAgente", "Plazo", "Codigo", "Descripcion", "Unidad", "Cantidad", "Precio", "Importe", "Dcto %", "Descuento", "SubTotal", "Impuesto", "Iva", "Total", "Costo", "CostoTotal", "Utilidad", "Item");
                break;
            case "VENTAS":
                break;
            case "PAGOS":
                break;
            case "REMISIONES":
                break;
            case "RESUMEN":
                $this->campos_consulta = array("Cliente", "Nombre", "Importe", "Descuento", 'SubTotal', 'Iva', 'Total', 'Costo', 'Utilidad', 'PorcentajeUtilidad');
                break;
            case "MENSUAL":
                $this->campos_consulta = array("Cliente", "NombreCliente", "SubTotal", "Utilidad");
                break;
            case "POTENCIALES":
                $this->campos_consulta = array("Numero", "Nombre", "Plazo", "TotalRemisiones");
                break;
            case "CORTE":
                $this->campos_consulta = array("Remision", "Serie", "Folio", "Cliente", "Agente", "Fecha", "Plazo", "Tipo", "Unidad", "Pedido", "Solicita", "Referencia", "Destino", "Almacen", "TeleMarketing", "Os", "Eq", "Rq", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Costo", "Comision", "Utilidad", "FormaPago", "Obs", "TipoCambio", "Hora", "Facturada", "Corte", "SuPago", "EnEfectivo", "EnTarjetas", "EnVales", "EnCheque", "Lugar", "Personas", "Status", "MotivoBaja", "Equipo", "Usuario", "Periodo");
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numerocliente = $numerocliente;
        $this->numeroagente = $numeroagente;
        $this->claveserie = $claveserie;
        $this->claveformapago = $claveformapago;
        $this->tipo = $tipo;
        $this->status = $status;
        $this->reporte = $reporte;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
            'Z' => 15,
            'AA' => 15,
            'AB' => 15,
            'AC' => 15,
            'AD' => 15,
            'AE' => 15,
            'AF' => 15,
            'AG' => 15,
            'AH' => 15,
            'AI' => 15,
            'AJ' => 15,
            'AK' => 15,
            'AL' => 15,
            'AM' => 15,
            'AN' => 15,
            'AO' => 15,
            'AP' => 15,
            'AQ' => 15,
            'AR' => 15,
            'AS' => 15,
            'AT' => 15,
            'AU' => 15,
            'AV' => 15,
            'AW' => 15,
            'AX' => 15,
            'AY' => 15,
            'AZ' => 15          
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relación Remisiones'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numerocliente=$this->numerocliente;
        $numeroagente=$this->numeroagente;
        $claveserie=$this->claveserie;
        $claveformapago=$this->claveformapago;
        $tipo=$this->tipo;
        $status=$this->status;
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
                $data = DB::table('Remisiones as r')
                ->select('Remision', 'Serie', 'Folio', 'Cliente', 'Agente', 'Fecha', DB::raw("FORMAT(Fecha, 'yyyy-MM-dd') as Fecha"), 'Plazo', 'Tipo', 'Unidad', 'Pedido', 'Solicita', 'Referencia', 'Destino', 'Almacen', 'TeleMarketing', 'Os', 'Eq', 'Rq', 'Importe', 'Descuento', 'SubTotal', 'Iva', 'Total', 'Costo', 'Comision', 'Utilidad', 'FormaPago', 'Obs', 'TipoCambio', 'Hora', 'Facturada', 'Corte', 'SuPago', 'EnEfectivo', 'EnTarjetas', 'EnVales', 'EnCheque', 'Lugar', 'Personas', 'Status', 'MotivoBaja', 'Equipo', 'Usuario', 'Periodo')
                //->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('r.Fecha', '>=', $fechainicio)->whereDate('r.Fecha', '<=', $fechaterminacion)
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
                break;
            case "PRODUCTOS":
                $data = DB::table('Remisiones as r')
                ->leftjoin('Clientes as c', 'r.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'r.Agente', '=', 'a.Numero')
                ->leftjoin('Remisiones Detalles as rd', 'r.Remision', '=', 'rd.Remision')
                ->select('r.Remision', DB::raw("FORMAT(r.Fecha, 'yyyy-MM-dd') as Fecha"), 'r.Cliente', 'c.Nombre AS NombreCliente', 'r.Agente', "a.Nombre AS NombreAgente", 'r.Plazo', 'rd.Codigo', 'rd.Descripcion', 'rd.Unidad', 'rd.Cantidad', 'rd.Precio', 'rd.Importe', 'rd.Dcto AS Dcto %', 'rd.Descuento', 'rd.SubTotal', 'rd.Impuesto', 'rd.Iva', 'rd.Total', 'rd.Costo', 'rd.CostoTotal', 'rd.Utilidad', 'rd.Item')
                //->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('r.Fecha', '>=', $fechainicio)->whereDate('r.Fecha', '<=', $fechaterminacion)
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
                //->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('r.Fecha', '>=', $fechainicio)->whereDate('r.Fecha', '<=', $fechaterminacion)
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
                break;
            case "MENSUAL":
                $data = DB::table("Clientes as c")
                ->select('c.Numero AS Cliente', 'c.Nombre AS NombreCliente')
                            ->addselect([
                                'SubTotal' => Remision::select(DB::raw("SUM(SubTotal)"))->whereColumn('Cliente', 'c.Numero')
														//->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
                                                        ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
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
														//->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
                                                        ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
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
                break;
            case "POTENCIALES":
                $data = DB::table('Remisiones as r')
                ->leftjoin('Clientes as c', 'r.Cliente', '=', 'c.Numero')
                ->select('c.Numero', 'c.Nombre', 'r.Plazo', DB::raw("SUM(r.Total) as TotalRemisiones"))
                //->whereBetween('r.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('r.Fecha', '>=', $fechainicio)->whereDate('r.Fecha', '<=', $fechaterminacion)
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
                break;
            case "CORTE":
                break;
        }
        return $data;
    }
}
