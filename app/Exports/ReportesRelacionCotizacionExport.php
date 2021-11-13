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
use DB;

class ReportesRelacionCotizacionExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numerocliente;
    private $tipo;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $tipo, $status, $reporte, $numerodecimales, $empresa){
        if($reporte == 'GENERAL'){
            $this->campos_consulta = array("Cotizacion", "Cliente", "Nombre", "Fecha", "Plazo", "Tipo", "Referencia", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Obs", "Status", "MotivoBaja", "Usuario");
        }else{
            $this->campos_consulta = array("Cotizacion", "Cliente", "Nombre", "Fecha", "Plazo", "Tipo", "Referencia", "Codigo", "Descripcion", "Unidad", "Cantidad", "Precio", "Importe", "Descuento", "SubTotal", "Iva", "Total", "Obs", "Status", "MotivoBaja", "Usuario");
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numerocliente = $numerocliente;
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
        return 'Relación Cotizaciones'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numerocliente=$this->numerocliente;
        $tipo=$this->tipo;
        $status=$this->status;
        if($reporte == "GENERAL"){
            $data = DB::table('Cotizaciones as co')
            ->leftjoin('Clientes as c', 'co.Cliente', '=', 'c.Numero')
            ->select('co.Cotizacion', 'co.Cliente', 'c.Nombre', DB::raw("FORMAT(co.Fecha, 'yyyy-MM-dd') as Fecha"), 'co.Plazo', 'co.Tipo', 'co.Referencia', 'co.Importe', 'co.Descuento', 'co.SubTotal', 'co.Iva', 'co.Total', 'co.Obs', 'co.Status', 'co.MotivoBaja', 'co.Usuario')
            //->whereBetween('co.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('co.Fecha', '>=', $fechainicio)->whereDate('co.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numerocliente) {
                if($numerocliente != ""){
                    $q->where('co.Cliente', $numerocliente);
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('co.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('co.Status', $status);
                }
            })
            ->orderby('co.Serie', 'ASC')
            ->orderby('co.Folio', 'ASC')
            ->get();
        }else{
            $data = DB::table('Cotizaciones as co')
            ->leftjoin('Clientes as c', 'co.Cliente', '=', 'c.Numero')
            ->leftjoin('Cotizaciones Detalles as cod', 'co.Cotizacion', '=', 'cod.Cotizacion')
            ->select('co.Cotizacion', 'co.Cliente', 'c.Nombre', DB::raw("FORMAT(co.Fecha, 'yyyy-MM-dd') as Fecha"), 'co.Plazo', 'co.Tipo', 'co.Referencia', 'cod.Codigo', 'cod.Descripcion', 'cod.Unidad', 'cod.Cantidad', 'cod.Precio', 'cod.Importe', 'cod.Descuento', 'cod.SubTotal', 'cod.Iva', 'cod.Total', 'co.Obs', 'co.Status', 'co.MotivoBaja', 'co.Usuario')
            //->whereBetween('co.Fecha', [$fechainicio, $fechaterminacion])
            ->whereDate('co.Fecha', '>=', $fechainicio)->whereDate('co.Fecha', '<=', $fechaterminacion)
            ->where(function($q) use ($numerocliente) {
                if($numerocliente != ""){
                    $q->where('co.Cliente', $numerocliente);
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('co.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('co.Status', $status);
                }
            })
            ->orderby('co.Serie', 'ASC')
            ->orderby('co.Folio', 'ASC')
            ->get();
        }
        return $data;
    }
}
