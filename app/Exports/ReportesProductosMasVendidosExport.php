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

class ReportesProductosMasVendidosExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $ordenarportotal;
    private $resumen;
    private $buscarenajuste;
    private $numerodecimales;
    private $empresa;
    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $numeroagente, $numeromarca, $numerolinea, $numeroalmacen, $codigo, $claveserie, $tipo, $departamento, $documentos, $status, $reporte, $ordenarportotal, $resumen, $buscarenajuste, $numerodecimales, $empresa){    
        switch($reporte){
            case "PORMARCAS":
                $this->campos_consulta = array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
                break;
            case "PORLINEAS":
                $this->campos_consulta = array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
                break;
            case "PORCLIENTES":
                $this->campos_consulta = array('Cliente', 'Nombre', 'NombreAgente', 'Codigo', 'Producto', 'Unidad', 'Ubicacion', 'Marca', 'NombreMarca', 'Linea', 'NombreLinea', 'Utilidad', 'Cantidad', 'SubTotal', 'Iva', 'Total', 'Almacen', 'FechaUltimaCompra', 'Dias', 'Costo', 'FechaUltimaVenta');
                break;
            case "PORCODIGOS":
                $this->campos_consulta = array('Codigo', 'Producto', 'Unidad', 'Marca', 'Linea', 'Ubicacion', 'Cantidad', 'Costo', 'Venta', 'Almacen', 'Existencias');
                break;
            case "CRUCEAJUSTE":
                $this->campos_consulta = array('Codigo', 'Producto', 'Marca', 'Linea', 'Almacen', 'CantidadDelAjuste', 'MasVendidas');
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
        $this->ordenarportotal = $ordenarportotal;
        $this->resumen = $resumen;
        $this->buscarenajuste = $buscarenajuste;
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
        return 'Relación Productos Mas Vendidos-'.$this->reporte;
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
        $ordenarportotal=$this->ordenarportotal;
        $resumen=$this->resumen;
        $buscarenajuste=$this->buscarenajuste;
        switch($reporte){
            case "PORMARCAS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
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
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORLINEAS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
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
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORCLIENTES":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->join('Agentes as a', 'f.Agente', '=', 'a.Numero')
                            ->select('f.Cliente', 'c.Nombre', 'fd.Codigo', 'p.Producto', 'p.Unidad', 'a.Nombre as NombreAgente', 'p.Ubicacion', 'm.Numero as Marca', 'm.Nombre AS NombreMarca', 'l.Numero as Linea', 'l.Nombre as NombreLinea', DB::raw("SUM(fd.Utilidad) AS Utilidad"), DB::raw("SUM(fd.Cantidad) AS Cantidad"), DB::raw("SUM(fd.SubTotal) AS SubTotal"), DB::raw("SUM(fd.Iva) AS Iva"),  DB::raw("SUM(fd.Total) AS Total"), 'fd.Almacen', DB::raw("p.[Fecha Ultima Compra] as FechaUltimaCompra"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate()) as Dias"), 'fd.Costo', DB::raw("p.[Fecha Ultima Venta] as FechaUltimaVenta") )
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
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
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('f.Cliente', 'c.Nombre', 'fd.Costo', 'f.Agente', 'fd.Codigo', 'p.Producto', 'f.Unidad', 'm.Numero', 'm.Nombre', 'l.Numero', 'l.Nombre', 'p.Ubicacion', 'p.Unidad', 'a.Nombre', DB::raw("p.[Fecha Ultima Venta]"), DB::raw("p.[Fecha Ultima Compra]"), DB::raw("datediff(dd, p.[Fecha Ultima Compra], getdate())"), 'fd.Almacen')
                            ->orderby(DB::raw("SUM(fd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORCODIGOS":
                $data = DB::table('Facturas as f')
                            ->join('Facturas Detalles as fd', 'f.Factura', '=', 'fd.Factura')
                            ->join('Productos as p', 'p.Codigo', '=', 'fd.Codigo')
                            ->join('Existencias as e', 'fd.Codigo', '=', 'e.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->join('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                            ->select('fd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre as Marca', 'l.Nombre as Linea', 'p.Ubicacion', DB::raw("SUM(fd.Cantidad) AS Cantidad"), 'p.Costo', 'p.Venta', 'fd.Almacen', 'e.Existencias' )
                            ->whereColumn("fd.Almacen", "e.Almacen")
                            ->whereDate('f.Fecha', '>=', $fechainicio)->whereDate('f.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numerocliente) {
                                if($numerocliente != ""){
                                    $q->whereIn('f.Cliente', array($numerocliente));
                                }
                            })
                            ->where(function($q) use ($numeroagente) {
                                if($numeroagente != ""){
                                    $q->whereIn('f.Agente', array($numeroagente));
                                }
                            })
                            ->where(function($q) use ($numeromarca) {
                                if($numeromarca != ""){
                                    $q->whereIn('m.Numero', array($numeromarca));
                                }
                            })
                            ->where(function($q) use ($numerolinea) {
                                if($numerolinea != ""){
                                    $q->whereIn('l.Numero', array($numerolinea));
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('fd.Almacen', array($numeroalmacen));
                                }
                            })
                            ->where(function($q) use ($claveserie) {
                                if($claveserie != ""){
                                    $q->whereIn('f.Serie', array($claveserie));
                                }
                            })
                            ->where(function($q) use ($codigo) {
                                if($codigo != ""){
                                    $q->whereIn('fd.Codigo', array($codigo));
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
                            ->where(function($q) use ($tipo) {
                                if($tipo != 'TODOS'){
                                    $q->where('f.Tipo', $tipo);
                                }
                            })
                            ->groupby('fd.Codigo', 'p.Producto', 'f.Unidad', 'p.Unidad', 'm.Nombre', 'l.Nombre', 'p.Costo', 'p.Venta', 'fd.Almacen', 'e.Existencias', 'p.Ubicacion')
                            ->orderby(DB::raw("SUM(fd.Cantidad)"), 'DESC')
                            ->get();
                break;
            case "CRUCEAJUSTE":
                //filtros en consulta
                $wheres = "";
                $wheres = $wheres ." and f.fecha >= '" .$fechainicio. "' and f.fecha <= '" .$fechaterminacion. "'";
                if($numerocliente != ""){
                    //$wheres = $wheres . "and f.Cliente = ".$numerocliente." ";
                    $wheres = $wheres . "and f.Cliente in (".$numerocliente.")";
                }
                if($numeroagente != ""){
                    //$wheres = $wheres . "and f.Agente = ".$numeroagente." ";
                    $wheres = $wheres . "and f.Agente in (".$numeroagente.")";
                }
                if($numeromarca != ""){
                    $wheres = $wheres . "and m.Numero in (".$numeromarca.")";
                }
                if($numerolinea != ""){
                    $wheres = $wheres . "and l.Numero in (".$numerolinea.")";
                }
                if($numeroalmacen != ""){
                    $wheres = $wheres . "and d.Almacen in (".$numeroalmacen.")";
                }
                if($claveserie != ""){
                    //$wheres = $wheres . "and f.Serie = '".$claveserie."' ";
                    $wheres = $wheres . "and f.Serie in (".$claveserie.")";
                }
                if($codigo != ""){
                    $wheres = $wheres . "and d.Codigo in ('".$codigo."')";
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
                $data = DB::table('Ajustes de Inventario Detalles as a')
                            ->select('a.Codigo', 't.Producto', 't.Marca', 't.Linea', 't.Almacen', DB::raw("SUM(a.Entradas - a.Salidas) as CantidadDelAjuste"), 't.Cantidad as MasVendidas')
                    
                            ->join(DB::raw('(select	d.Codigo, p.Producto, m.nombre as Marca, l.nombre as Linea, sum(d.cantidad) as Cantidad, d.Almacen, p.Costo
                                from	facturas f, [facturas detalles] d, productos p, marcas m, lineas l, clientes c
                                where	f.factura = d.factura and d.codigo = p.codigo and f.cliente = c.numero and p.marca = m.numero and p.linea = l.numero 	
                                '.$wheres.'
                                group by d.codigo, p.producto, m.numero, m.nombre, l.nombre, p.Costo, d.Almacen
                            ) t'), 
                            function($join)
                            {
                                $join->on('t.Codigo', '=', 'a.Codigo');
                            })
                            ->where(function($q) use ($buscarenajuste) {
                                if($buscarenajuste != ""){
                                    $q->where('a.Ajuste', $buscarenajuste);
                                }
                            })
                            ->groupby('a.Codigo', 't.Producto', 't.Marca', 't.Linea', 't.Almacen', 't.Cantidad')
                            ->orderBy('t.Cantidad', 'DESC')
                            ->get();
                break;
        }
        return $data;
    }

}
