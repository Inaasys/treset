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
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\OrdenCompra;
use App\OrdenCompraDetalle;
use App\Proveedor;
use App\Almacen;
use DB;

class ReportesRelacionOrdenCompraExport implements FromCollection,WithHeadings,WithTitle
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

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relación Ordenes Compra';
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        if($reporte == "RELACION"){
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'oc.Importe', 'oc.Descuento', 'oc.SubTotal', 'oc.Iva', 'oc.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
            if($this->numeroproveedor != ""){
                $data = $data->where('oc.Proveedor', $this->numeroproveedor);
            }
            if($this->numeroalmacen != ""){
                $data = $data->where('oc.Almacen', $this->numeroalmacen);
            }
            if($this->tipo != 'TODOS'){
                $data = $data->where('Tipo', $this->tipo);
            }
            if($this->status != 'TODOS'){
                $data = $data->where('Status', $this->status);
            }
        }else{
            $data = DB::table('Ordenes de Compra as oc')
            ->leftjoin('Proveedores as p', 'oc.Proveedor', '=', 'p.Numero')
            ->leftjoin('Ordenes de Compra Detalles as ocd', 'oc.Orden', '=', 'ocd.Orden')
            ->select('oc.Orden', 'oc.Proveedor', 'p.Nombre', 'oc.Fecha', 'oc.Plazo', 'oc.Almacen', 'oc.Tipo', 'oc.Referencia', 'ocd.Codigo', 'ocd.Descripcion', 'ocd.Unidad', 'ocd.Surtir as Por Surtir', 'ocd.Cantidad', 'ocd.Precio', 'ocd.Importe', 'ocd.Descuento', 'ocd.SubTotal', 'ocd.Iva', 'ocd.Total', 'oc.Obs', 'oc.Status', 'oc.MotivoBaja', 'oc.Usuario')
            ->whereBetween('oc.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('oc.Serie', 'ASC')
            ->orderby('oc.Folio', 'ASC')
            ->get();
            if($this->numeroproveedor != ""){
                $data = $data->where('oc.Proveedor', $this->numeroproveedor);
            }
            if($this->numeroalmacen != ""){
                $data = $data->where('oc.Almacen', $this->numeroalmacen);
            }
            if($this->tipo != 'TODOS'){
                $data = $data->where('Tipo', $this->tipo);
            }
            if($this->status != 'TODOS'){
                $data = $data->where('Status', $this->status);
            }
        }
        return $data;
    }
}
