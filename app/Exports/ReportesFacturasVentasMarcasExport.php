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
use App\Compra;
use App\CompraDetalle;
use App\Proveedor;
use App\Almacen;
use DB;

class ReportesFacturasVentasMarcasExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $numeromarca;
    private $numerolinea;
    private $numeroalmacen;
    private $codigo;
    private $claveserie;
    private $tipo;
    private $departamento;
    private $documentos;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;
    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $numeroagente, $numeromarca, $numerolinea, $numeroalmacen, $codigo, $claveserie, $tipo, $departamento, $documentos, $status, $reporte, $numerodecimales, $empresa){    
        switch($reporte){
            case "RELACIONUTILIDADES":
                $this->campos_consulta = array("Factura", "Fecha", "Remision", "Orden", "Producto", "NombreCliente", "Agente", "NombreAgente", "Almacen", "Codigo", "Marca", "Nombre", "Cantidad", "Precio", "Dcto", "Descuento", "SubTotal", "Iva", "Total", "Costo", "CostoTotal", "Utilidad", "FechaUltimaCompra", "FechaUltimaVenta", "Dias");
                break;
            case "UTILIDADCAMBIARIA":
                $this->campos_consulta = array("Factura", "Fecha", "Remision", "Orden", "Producto", "NombreCliente", "Agente", "NombreAgente", "Almacen", "Codigo", "Marca", "Nombre", "Cantidad", "Precio", "Dcto", "Descuento", "SubTotal", "Iva", "Total", "Costo", "CostoTotal", "Utilidad", "PorcentajeUtilidad", "CostoDeLista", "Moneda", "TipoDeCambio", "CostoDeListaMXN", "TotalCostoDeListaReal", "UtilidadPorCostoDeListaActualReal", "UtilidadPorTC", "CostoParaComision", "CostoTotalParaComision", "UtilidadParaComision");
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numerocliente = $numerocliente;
        $this->numeroagente = $numeroagente;
        $this->numeromarca = $numeromarca;
        $this->numerolinea = $numerolinea;
        $this->numeroalmacen = $numeroalmacen;
        $this->codigo = $codigo;
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
            'AJ' => 15            
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relación Facturas Ventas Por Marcas-'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $numerocliente=$this->numerocliente;
        $numeroagente=$this->numeroagente;
        $numeromarca=$this->numeromarca;
        $numerolinea=$this->numerolinea;
        $numeroalmacen=$this->numeroalmacen;
        $codigo=$this->codigo;
        $claveserie=$this->claveserie;
        $tipo=$this->tipo;
        $departamento=$this->departamento;
        $documentos=$this->documentos;
        $status=$this->status;
        $reporte = $this->reporte;
        switch($reporte){
            case "RELACIONUTILIDADES":
                $data = DB::table('Facturas as f')
                ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                ->leftjoin('Marcas as m', 'm.Numero', '=', 'p.Marca')
                ->join('Clientes as c', 'c.Numero', '=', 'f.Cliente')
                ->leftjoin('Agentes as a', 'a.Numero', '=', 'f.Agente')
                ->select("f.Factura", "f.Fecha", "fd.Remision", "fd.Orden", "p.Producto", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", "fd.Almacen", "fd.Codigo", "p.Marca", "m.Nombre", "fd.Cantidad", "fd.Precio", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Iva", "fd.Total", DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio,2) else fd.Costo end as Costo"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.CostoTotal end as CostoTotal"),DB::raw("case	when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then fd.SubTotal-round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.Utilidad end as Utilidad"),DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"),DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta"),DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"))
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
                ->where(function($q) use ($numeromarca) {
                    if($numeromarca != ""){
                        $q->whereIn('p.Marca', array($numeromarca));
                    }
                })
                ->where(function($q) use ($numerolinea) {
                    if($numerolinea != ""){
                        $q->whereIn('p.Linea', array($numerolinea));
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->whereIn('f.Serie', array($claveserie));
                    }
                })
                ->where(function($q) use ($numeroalmacen) {
                    if($numeroalmacen != ""){
                        $q->whereIn('fd.Almacen', array($numeroalmacen));
                    }
                })
                ->where(function($q) use ($codigo) {
                    if($codigo != ""){
                        $q->where('fd.Codigo', $codigo);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.Depto', $departamento);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        $q->where('f.Status', $status);
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
                ->orderby('f.Serie', 'ASC')
                ->orderby('f.Folio', 'ASC')
                ->orderby('fd.Item', 'ASC')
                ->get();
                break;
            case "UTILIDADCAMBIARIA":
                $data = DB::table('Facturas as f')
                ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                ->leftjoin('Marcas as m', 'm.Numero', '=', 'p.Marca')
                ->join('Clientes as c', 'c.Numero', '=', 'f.Cliente')
                ->leftjoin('Agentes as a', 'a.Numero', '=', 'f.Agente')
                ->select("f.Factura", "f.Fecha", "fd.Remision", "fd.Orden", "p.Producto", "c.Nombre as NombreCliente", "f.Agente", "a.Nombre as NombreAgente", "fd.Almacen", "fd.Codigo", "p.Marca", "m.Nombre", "fd.Cantidad", "fd.Precio", "fd.Dcto", "fd.Descuento", "fd.SubTotal", "fd.Iva", "fd.Total","fd.Costo", "fd.CostoTotal", "fd.Utilidad", DB::raw("case when fd.subtotal>0 then (fd.Utilidad/fd.SubTotal)*100 else 0 end as PorcentajeUtilidad"),"fd.CostoDeLista", "fd.Moneda", "fd.TipoDeCambio", DB::raw("fd.CostoDeLista*fd.TipoDeCambio as CostoDeListaMXN"),DB::raw("round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) as TotalCostoDeListaReal"),DB::raw("round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) as UtilidadPorCostoDeListaActualReal"),DB::raw("round(fd.Utilidad-(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad),2) as UtilidadPorTC"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio,2) else fd.Costo end as CostoParaComision"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.CostoTotal end as CostoTotalParaComision"),DB::raw("case when fd.moneda = 'USD' and round(fd.SubTotal-fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) > 0 then fd.SubTotal-round(fd.CostoDeLista*fd.TipoDeCambio*fd.Cantidad,2) else fd.Utilidad end as UtilidadParaComision"))
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
                ->where(function($q) use ($numeromarca) {
                    if($numeromarca != ""){
                        $q->whereIn('p.Marca', array($numeromarca));
                    }
                })
                ->where(function($q) use ($numerolinea) {
                    if($numerolinea != ""){
                        $q->whereIn('p.Linea', array($numerolinea));
                    }
                })
                ->where(function($q) use ($claveserie) {
                    if($claveserie != ""){
                        $q->whereIn('f.Serie', array($claveserie));
                    }
                })
                ->where(function($q) use ($numeroalmacen) {
                    if($numeroalmacen != ""){
                        $q->whereIn('fd.Almacen', array($numeroalmacen));
                    }
                })
                ->where(function($q) use ($codigo) {
                    if($codigo != ""){
                        $q->where('fd.Codigo', $codigo);
                    }
                })
                ->where(function($q) use ($tipo) {
                    if($tipo != 'TODOS'){
                        $q->where('f.Tipo', $tipo);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.Depto', $departamento);
                    }
                })
                ->where(function($q) use ($status) {
                    if($status != 'TODOS'){
                        $q->where('f.Status', $status);
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
                ->orderby('f.Serie', 'ASC')
                ->orderby('f.Folio', 'ASC')
                ->orderby('fd.Item', 'ASC')
                ->get();
                break;
        }
        return $data;
    }
}
