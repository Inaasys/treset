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
use App\Banco;
use App\Almacen;
use DB;

class ReportesRelacionCuentasPorPagarExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numeroproveedor;
    private $numerobanco;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroproveedor, $numerobanco, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "AGRUPARxPROVEEDOR":
                $this->campos_consulta = array("Pago", "Fecha", "Proveedor", "Banco", "Compra", "Remision", "Factura", "Transferencia", "Cheque", "Beneficiario", "Total", "Abono", "Saldo", "Anotacion", "MotivoBaja", "Status");
                break;
            case "AGRUPARxBANCO":
                $this->campos_consulta = array("Pago", "Fecha", "Proveedor", "Banco", "Compra", "Remision", "Factura", "Transferencia", "Cheque", "Beneficiario", "Total", "Abono", "Saldo", "Anotacion", "MotivoBaja", "Status");
                break;
            case "RELACIONPAGOS":
                $this->campos_consulta = array("Pago", "Fecha", "Proveedor", "Banco", "Abono", "Anotacion", "MotivoBaja", "Status");
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroproveedor = $numeroproveedor;
        $this->numerobanco = $numerobanco;
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
        return 'Relación CuentasPorPagar'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numeroproveedor=$this->numeroproveedor;
        $numerobanco=$this->numerobanco;
        switch($reporte){
            case "AGRUPARxPROVEEDOR":
                $data = DB::table('CxP as cxp')
                ->leftjoin('CxP Detalles as cxpd', 'cxp.Pago', '=', 'cxpd.Pago')
                ->leftjoin('Compras as c', 'cxpd.Compra', '=', 'c.Compra')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxpd.Pago', DB::raw("FORMAT(cxpd.Fecha, 'yyyy-MM-dd') as Fecha"), 'p.Numero', 'p.Nombre AS Proveedor', 'c.Saldo', 'b.Nombre as Banco', 'cxpd.Compra', 'c.Remision', 'c.Factura', 'c.Total', 'cxpd.Abono', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status', 'cxpd.Item')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('cxp.Serie', 'DESC')
                ->orderby('cxp.Folio', 'DESC')
                ->get();
                break;
            case "AGRUPARxBANCO":
                break;
            case "RELACIONPAGOS":
                $data = DB::table('CxP as cxp')
                ->leftjoin('Proveedores as p', 'cxp.Proveedor', '=', 'p.Numero')
                ->leftjoin('Bancos as b', 'cxp.Banco', '=', 'b.Numero')
                ->select('cxp.Pago', DB::raw("FORMAT(cxp.Fecha, 'yyyy-MM-dd') as Fecha"), 'p.Numero', 'p.Nombre AS Proveedor', 'b.Nombre as Banco', 'cxp.Transferencia', 'cxp.Cheque', 'cxp.Beneficiario', 'cxp.Abono', 'cxp.Anotacion', 'cxp.MotivoBaja', 'cxp.Status')
                ->whereDate('cxp.Fecha', '>=', $fechainicio)->whereDate('cxp.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numeroproveedor) {
                    if($numeroproveedor != ""){
                        $q->where('p.Numero', $numeroproveedor);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxp.Banco', $numerobanco);
                    }
                })
                ->orderby('cxp.Serie', 'DESC')
                ->orderby('cxp.Folio', 'DESC')
                ->get();
        }
        return $data;
    }
}
