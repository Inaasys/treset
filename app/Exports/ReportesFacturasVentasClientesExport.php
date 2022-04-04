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
use App\Factura;
use App\FacturaDetalle;
use App\NotaCliente;
use App\NotaClienteDetalle;
use App\Cliente;
use App\Agente;
use DB;

class ReportesFacturasVentasClientesExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $tipo;
    private $departamento;
    private $documentos;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $numeroagente, $claveserie, $tipo, $departamento, $documentos, $status, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
                $this->campos_consulta = array("Factura", "UUID", "Serie", "Folio", "Depto", "Tipo", "Cliente", "NombreCliente", "Agente", "NombreAgente", "Fecha", "Plazo", "Pedido", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Abonos", "Descuentos", "Saldo", "Costo", "Utilidad", "Moneda", "TipoCambio", "Obs", "Status", "MotivoBaja", "Usuario");
                break;
            case "PRODUCTOS":
                $this->campos_consulta = array("Factura", "Fecha", "Cliente", "NombreCliente", "Agente", "Tipo", "NombreAgente", "Plazo", "Codigo", "Descripcion", "Unidad", "Cantidad", "Precio", "Importe", "Dcto", "Descuento", "SubTotal", "Impuesto", "Iva", "Total", "Costo", "CostoTotal", "Utilidad", "Facturar", "Remision", "Orden", "Departamento", "Cargo", "Almacen", "Partida", "Item");
                break;
            case "VENTAS":
                break;
            case "PAGOS":
                break;
            case "FACTURAS":
                break;
            case "RESUMEN":
                $this->campos_consulta = array("Cliente", "Nombre", "Importe", "Descuento", 'SubTotal', 'Iva', 'Total', 'Costo', 'Utilidad', 'PorcentajeUtilidad');
                break;
            case "MENSUAL":
                $this->campos_consulta = array("Cliente", "NombreCliente", "SubTotal", "Utilidad");
                break;
            case "POTENCIALES":
                $this->campos_consulta = array("Numero", "Nombre", "Plazo", "Credito", "Bloquear", "Saldo", "TotalFacturas");
                break;
            case "COMPARATIVO MENSUAL":
                $this->campos_consulta = array("Cliente", "NombreCliente");
                //obtener todos los meses y años entre la fecha inicial y final del reporte
                $todaslasfechas = array();
                for($i=date($fechainicialreporte);$i<=date($fechafinalreporte);$i = date("Y-m-d", strtotime($i ."+ 1 days"))){
                    if (in_array(date("Y-m", strtotime($i)), $todaslasfechas)) {   
                    }else{
                        array_push($todaslasfechas, date("Y-m", strtotime($i)));
                        array_push($this->campos_consulta, "SubTotal".date("Y-m", strtotime($i)));
                        array_push($this->campos_consulta, "Utilidad".date("Y-m", strtotime($i)));
                        array_push($this->campos_consulta, "PorcentajeUtilidad".date("Y-m", strtotime($i)));
                    }                
                }
                break;
            case "COMPARATIVO ANUAL":
                $this->campos_consulta = array("Cliente", "NombreCliente");
                //obtener todos los meses y años entre la fecha inicial y final del reporte
                $todaslasfechas = array();
                for($i=date($fechainicialreporte);$i<=date($fechafinalreporte);$i = date("Y-m-d", strtotime($i ."+ 1 days"))){
                    if (in_array(date("Y", strtotime($i)), $todaslasfechas)) {   
                    }else{
                        array_push($todaslasfechas, date("Y", strtotime($i)));
                        array_push($this->campos_consulta, "Facturado".date("Y", strtotime($i)));
                    }                
                }
                break;
            case "NOTAS DE CREDITO":
                $this->campos_consulta = array("Comprobante", "Documento", "Fecha", "SubTotal", "Iva", "Total");
                break;
            case "NO FACTURADOS":
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numerocliente = $numerocliente;
        $this->numeroagente = $numeroagente;
        $this->claveserie = $claveserie;
        $this->tipo = $tipo;
        $this->departamento = $departamento;
        $this->documentos = $documentos;
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
        return 'Relación Facturas'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $numerocliente=$this->numerocliente;
        $numeroagente=$this->numeroagente;
        $claveserie=$this->claveserie;
        $tipo=$this->tipo;
        $departamento=$this->departamento;
        $documentos=$this->documentos;
        $status=$this->status;
        $reporte = $this->reporte;
        switch($reporte){
            case "UTILIDAD":
                break;
            case "GENERAL":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'f.Agente', '=', 'a.Numero')
                ->select("f.Factura", "f.UUID", "f.Serie", "f.Folio", "f.Depto", "f.Tipo", "f.Cliente", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", DB::raw("FORMAT(f.Fecha, 'yyyy-MM-dd') as Fecha"), "f.Plazo", "f.Pedido", "f.Importe", "f.Descuento", "f.SubTotal", "f.Iva", "f.Total", "f.Abonos", "f.Descuentos", "f.Saldo", "f.Costo", "f.Utilidad", "f.Moneda", "f.TipoCambio", "f.Obs", "f.Status", "f.MotivoBaja", "f.Usuario")
                //->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
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
                break;
            case "PRODUCTOS":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->leftjoin('Agentes as a', 'f.Agente', '=', 'a.Numero')
                ->leftjoin('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->select("f.Factura", DB::raw("FORMAT(f.Fecha, 'yyyy-MM-dd') as Fecha"), "f.Cliente", "c.Nombre as NombreCliente", "f.Agente", "c.Tipo", "a.Nombre as NombreAgente", "f.Plazo", "fd.Codigo", "fd.Descripcion", "fd.Unidad", "fd.Cantidad", "fd.Precio", "fd.Importe", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Impuesto", "fd.Iva", "fd.Total", "fd.Costo", "fd.CostoTotal", "fd.Utilidad", "fd.Facturar", "fd.Remision", "fd.Orden", "fd.Departamento", "fd.Cargo", "fd.Almacen", "fd.Partida", "fd.Item")
                //->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
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
                //->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
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
                break;
            case "MENSUAL":
                $data = DB::table("Clientes as c")
                ->select('c.Numero AS Cliente', 'c.Nombre AS NombreCliente')
                            ->addselect([
                                'SubTotal' => Factura::select(DB::raw("SUM(SubTotal)"))->whereColumn('Cliente', 'c.Numero')
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
                break;
            case "POTENCIALES":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->select('c.Numero', 'c.Nombre', 'f.Plazo', 'c.Credito', 'c.Bloquear', 'c.Saldo', DB::raw("SUM(f.Total) as TotalFacturas"))
                //->whereBetween('f.Fecha', [$fechainicio, $fechaterminacion])
                ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
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
                break;
            case "NOTAS DE CREDITO":
                $facturas = DB::table('Facturas')
                                ->select(DB::raw("'Ingreso' as Comprobante"), 'Factura as Documento', DB::raw("FORMAT(Fecha, 'yyyy-MM-dd') as Fecha"), 'SubTotal', 'Iva', 'Total')
                                //->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
                                ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion);
                $data = DB::table('Notas Cliente')
                            ->select(DB::raw("'Egreso' as Comprobante"), 'Nota as Documento', DB::raw("FORMAT(Fecha, 'yyyy-MM-dd') as Fecha"), DB::raw("-SubTotal AS SubTotal"), DB::raw("-Iva AS Iva"), DB::raw("-Total AS Total"))
                            //->whereBetween('Fecha', [$fechainicio, $fechaterminacion])
                            ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                            ->union($facturas)
                            ->orderby('Fecha', 'ASC')
                            ->get();
                break;
            case "NO FACTURADOS":
                break;
        }
        //return $data;
        return collect($data);
    }
}
