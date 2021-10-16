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
use App\OrdenCompra;
use App\OrdenCompraDetalle;
use App\Proveedor;
use App\Almacen;
use DB;

class ReportesRelacionOrdenCompraExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $tipo;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroproveedor, $numeroalmacen, $tipo, $status, $reporte, $numerodecimales, $empresa){
        if($reporte == 'RELACION'){
            $this->campos_consulta = array("Orden", "Proveedor", "Nombre", "Fecha", "Plazo", "Almacen", "Tipo", "Referencia", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Obs", "Status", "MotivoBaja", "Usuario");
        }else{
            $this->campos_consulta = array("Orden", "Proveedor", "Nombre", "Fecha", "Plazo", "Almacen", "Tipo", "Referencia", "Codigo", "Descripcion", "Unidad", "Por Surtir", "Cantidad", "Precio", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Obs", "Status", "MotivoBaja", "Usuario");
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroproveedor = $numeroproveedor;
        $this->numeroalmacen = $numeroalmacen;
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
            'AJ' => 15            
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relación Ordenes Compra'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numeroproveedor=$this->numeroproveedor;
        $numeroalmacen=$this->numeroalmacen;
        $tipo=$this->tipo;
        $status=$this->status;
        if($reporte == "RELACION"){
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'oc.Importe', 'oc.Descuento', 'oc.SubTotal', 'oc.Iva', 'oc.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('oc.Proveedor', $numeroproveedor);
                }
            })
            ->where(function($q) use ($numeroalmacen) {
                if($numeroalmacen != ""){
                    $q->whereIn('oc.Almacen', array($numeroalmacen));
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('oc.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('oc.Status', $status);
                }
            })
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
        }else{
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->leftjoin('Ordenes de Compra Detalles as ocd', 'oc.Orden', '=', 'ocd.Orden')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'ocd.Codigo', 'ocd.Descripcion', 'ocd.Unidad', 'ocd.Surtir as Por Surtir', 'ocd.Cantidad', 'ocd.Precio', 'ocd.Importe', 'ocd.Descuento', 'ocd.SubTotal', 'ocd.Iva', 'ocd.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('oc.Proveedor', $numeroproveedor);
                }
            })
            ->where(function($q) use ($numeroalmacen) {
                if($numeroalmacen != ""){
                    $q->whereIn('oc.Almacen', array($numeroalmacen));
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('oc.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('oc.Status', $status);
                }
            })
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
        }
        return $data;
    }
}
