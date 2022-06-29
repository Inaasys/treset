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
use App\FolioComprobanteFactura;
use App\FolioComprobanteNota;
use App\FolioComprobantePago;
use App\FolioComprobanteTraslado;
use App\Comprobante;
use DB;

class ReporteRelacionTimbresUtilizadosExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $claveserie;
    private $tipocomprobante;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $claveserie, $tipocomprobante, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "GENERAL":
                $this->campos_consulta = array("Comprobante", "Tipo", "Serie", "Folio", "UUID", "EmisorRfc", "ReceptorRfc", "FormaPago", "MetodoPago", "UsoCfdi");
                break;
            case "TOTALES":
                $this->campos_consulta = array("Total");
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->claveserie = $claveserie;
        $this->tipocomprobante = $tipocomprobante;
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
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relacion Timbres Utilizados'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $claveserie=$this->claveserie;
        $reporte = $this->reporte;
        $tipocomprobante=$this->tipocomprobante;
        $foliosF = FolioComprobanteFactura::select('Serie');
        $foliosN = FolioComprobanteNota::select('Serie');
        $foliosP = FolioComprobantePago::select('Serie');
        $foliosT = FolioComprobanteTraslado::select('Serie')
        ->union($foliosF)
        ->union($foliosN)
        ->union($foliosP)->get();
        switch($reporte){
            case "GENERAL":
                $data = DB::table('Comprobantes')
                    ->select('Comprobante', 'Tipo', 'Serie', 'Folio', 'UUID', 'EmisorRfc', 'ReceptorRfc', 'FormaPago', 'MetodoPago', 'UsoCfdi')
                    ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                    ->whereIn('Serie', $foliosT)
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != null){
                            $q->where('Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($tipocomprobante) {
                        if($tipocomprobante != 'TODOS'){
                            $q->where('Comprobante', $tipocomprobante);
                        }
                    })
                    ->orderby('Folio')
                    ->orderby('Comprobante')
                    ->get();
                break;
            case "TOTALES":
                $data = DB::table('Comprobantes')
                    ->select(DB::raw('COUNT(Comprobante) as Total'))
                    ->whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != null){
                            $q->where('Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($tipocomprobante) {
                        if($tipocomprobante != 'TODOS'){
                            $q->where('Comprobante', $tipocomprobante);
                        }
                    })
                    ->get();
                break;
        }
        return $data;
    }
}
