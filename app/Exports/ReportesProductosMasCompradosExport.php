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

class ReportesProductosMasCompradosExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numeroproveedor;
    private $numeroalmacen;
    private $numeromarca;
    private $numerolinea;
    private $ordenarportotal;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroproveedor, $numeroalmacen, $numeromarca, $numerolinea, $ordenarportotal, $status, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "PORMARCAS":
                $this->campos_consulta = array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
                break;
            case "PORLINEAS":
                $this->campos_consulta = array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
                break;
            case "PORPROVEEDORES":
                $this->campos_consulta = array('Proveedor', 'Codigo', 'Producto', 'Unidad', 'Fecha', 'Tipo', 'Almacen', 'Factura', 'Remision', 'Marca', 'Linea', 'Cantidad', 'Precio', 'Importe', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total');
                break;
            case "PORCODIGOS":
                $this->campos_consulta = array('Codigo', 'Producto', 'Unidad', 'Marca', 'Linea', 'Ubicacion', 'Cantidad', 'Costo', 'Venta', 'Almacen', 'Existencias');
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroproveedor = $numeroproveedor;
        $this->numeroalmacen = $numeroalmacen;
        $this->numeromarca = $numeromarca;
        $this->numerolinea = $numerolinea;
        $this->ordenarportotal = $ordenarportotal;
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
        return 'Relación Productos Mas Comprados-'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numeroproveedor = $this->numeroproveedor;
        $numeroalmacen = $this->numeroalmacen;
        $numeromarca = $this->numeromarca;
        $numerolinea = $this->numerolinea;
        $status = $this->status;
        $ordenarportotal = $this->ordenarportotal;
        $campos_consulta = $this->campos_consulta;
        switch($reporte){
            case "PORMARCAS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORLINEAS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORPROVEEDORES":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Tipo', 'c.Almacen', 'c.Factura', 'c.Remision', 'm.Nombre AS Marca', 'l.Nombre AS Linea', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'cd.Precio', 'cd.Importe', 'cd.Dcto', 'cd.Descuento',  DB::raw("SUM(cd.SubTotal) AS SubTotal"),  DB::raw("SUM(cd.Iva) AS Iva"),  DB::raw("SUM(cd.Total) AS Total"),  )
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('c.Proveedor', 'cd.Codigo', 'p.Producto', 'p.Unidad', 'c.Fecha', 'c.Factura', 'c.Remision', 'c.Tipo', 'c.Almacen', 'm.Nombre', 'l.Nombre', 'cd.Importe', 'cd.Precio', 'cd.Dcto', 'cd.Descuento')
                            ->orderby(DB::raw("SUM(cd.".$ordenarportotal.")"), 'DESC')
                            ->get();
                break;
            case "PORCODIGOS":
                $data = DB::table('Compras as c')
                            ->join('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
                            ->join('Productos as p', 'p.Codigo', '=', 'cd.Codigo')
                            ->join('Existencias as e', 'cd.Codigo', '=', 'e.Codigo')
                            ->join('Proveedores as prov', 'c.Proveedor', '=', 'prov.Numero')
                            ->join('Marcas as m', 'p.Marca', '=', 'm.Numero')
                            ->join('Lineas as l', 'p.Linea', '=', 'l.Numero')
                            ->select('cd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre AS Marca', 'l.Nombre AS Linea', 'p.Ubicacion', DB::raw("SUM(cd.Cantidad) AS Cantidad"), 'p.Costo', 'p.Venta', 'c.Almacen', 'e.Existencias')
                            ->whereColumn("c.Almacen", "e.Almacen")
                            ->whereDate('c.Fecha', '>=', $fechainicio)->whereDate('c.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroproveedor) {
                                if($numeroproveedor != ""){
                                    $q->where('c.Proveedor', $numeroproveedor);
                                }
                            })
                            ->where(function($q) use ($numeroalmacen) {
                                if($numeroalmacen != ""){
                                    $q->whereIn('c.Almacen', array($numeroalmacen));
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
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('c.Status', $status);
                                }
                            })
                            ->groupby('cd.Codigo', 'p.Producto', 'p.Unidad', 'm.Nombre', 'l.Nombre', 'p.Costo', 'p.Venta', 'c.Almacen', 'e.Existencias', 'p.Ubicacion')
                            ->orderby(DB::raw("SUM(cd.Cantidad)"), 'DESC')
                            ->get();
                break;
        }
        return $data;
    }

}
