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

class ReportesRelacionContraReciboExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numeroproveedor;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroproveedor, $reporte, $numerodecimales, $empresa){
        if($reporte == 'GENERAL'){
            $this->campos_consulta = array("ContraRecibo", "Proveedor", "Nombre", "Fecha", "SubTotal", "Iva", "Total", "Status");
        }else{
            $this->campos_consulta = array("ContraRecibo", "Proveedor", "Nombre", "Compra", "Movimiento", "Fecha", "Remision", "Factura", "FechaAPagar", "SubTotal", "Iva", "Total", "Status");
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroproveedor = $numeroproveedor;
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
        return 'Relación ContraRecibos'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numeroproveedor=$this->numeroproveedor;
        if($reporte == "GENERAL"){
            $data = DB::table('ContraRecibos as cr')
            ->leftjoin('Proveedores as p', 'cr.Proveedor', '=', 'p.Numero')
            ->leftjoin('ContraRecibos Detalles as crd', 'cr.ContraRecibo', '=', 'crd.ContraRecibo')
            ->leftjoin('Compras as c', 'crd.Compra', '=', 'c.Compra')
            ->select('cr.ContraRecibo', 'cr.Proveedor', 'p.Nombre', DB::raw("FORMAT(cr.Fecha, 'yyyy-MM-dd') as Fecha"), DB::raw("SUM(c.SubTotal) as SubTotal"), DB::raw("SUM(c.Iva) as Iva"), DB::raw("SUM(c.Total) as Total"), 'cr.Status')
            //->whereBetween('cr.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('cr.Fecha', '>=', $fechainicio)->whereDate('cr.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('cr.Proveedor', $numeroproveedor);
                }
            })
            ->groupby('cr.ContraRecibo', 'cr.Fecha', 'cr.Proveedor', 'p.Nombre', 'cr.Status')
            ->orderby('cr.Fecha', 'ASC')
            ->get();
        }else{
            $data = DB::table('ContraRecibos as cr')
            ->leftjoin('Proveedores as p', 'cr.Proveedor', '=', 'p.Numero')
            ->leftjoin('ContraRecibos Detalles as crd', 'cr.ContraRecibo', '=', 'crd.ContraRecibo')
            ->leftjoin('Compras as c', 'crd.Compra', '=', 'c.Compra')
            ->select('cr.ContraRecibo', 'cr.Proveedor', 'p.Nombre', 'crd.Compra', 'c.Movimiento', DB::raw("FORMAT(cr.Fecha, 'yyyy-MM-dd') as Fecha"), 'c.Remision', 'c.Factura', DB::raw("FORMAT(crd.FechaAPagar, 'yyyy-MM-dd') as FechaAPagar"), 'c.SubTotal', 'c.Iva', 'c.Total', 'cr.Status')
            //->whereBetween('cr.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('cr.Fecha', '>=', $fechainicio)->whereDate('cr.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('cr.Proveedor', $numeroproveedor);
                }
            })
            ->orderby('cr.Fecha', 'ASC')
            ->get();
        }
        return $data;
    }
}
