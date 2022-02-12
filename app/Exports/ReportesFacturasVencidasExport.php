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
use App\CuentaXCobrar;
use App\CuentaXCobrarDetalle;
use App\Cliente;
use App\Agente;
use DB;
use Illuminate\Support\Collection;

class ReportesFacturasVencidasExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechacorte;
    private $numerocliente;
    private $claveserie;
    private $departamento;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechacorte, $numerocliente, $claveserie, $departamento, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "GENERAL":
                $this->campos_consulta = array("Factura", "Serie", "Folio", "Esquema", "Status", "Cliente", "NombreCliente", "Fecha", "Depto", "FormaPago", "SubTotal", "Iva", "Total", "Abonos", "Descuentos", "Saldo");
                break;
            case "DETALLES":
                break;
        }
        $this->fechacorte = $fechacorte;
        $this->numerocliente = $numerocliente;
        $this->claveserie = $claveserie;
        $this->departamento = $departamento;
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
        return 'Facturas Vencidas'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechacorte = date($this->fechacorte);
        $numerocliente=$this->numerocliente;
        $claveserie=$this->claveserie;
        $departamento=$this->departamento;
        $reporte = $this->reporte;
        switch($reporte){
            case "GENERAL":
                $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select('f.Factura', 'f.Serie', 'f.Folio', 'f.Esquema', 'f.Status', 'f.Cliente', 'c.Nombre as NombreCliente', 'f.Fecha', 'f.Depto', 'f.FormaPago', 'f.SubTotal', 'f.Iva', 'f.Total', 'f.Abonos', 'f.Descuentos', 'f.Saldo')
                    ->where('f.Esquema', 'CFDI')
                    ->where('f.FormaPago', '99')
                    ->where('f.Status', 'POR COBRAR')
                    ->where('f.Fecha', '<=', $fechacorte)
                    ->where(function($q) use ($numerocliente) {
                        if($numerocliente != ""){
                            $q->where('f.Cliente', $numerocliente);
                        }
                    })
                    ->where(function($q) use ($claveserie) {
                        if($claveserie != ""){
                            $q->where('f.Serie', $claveserie);
                        }
                    })
                    ->where(function($q) use ($departamento) {
                        if($departamento != 'TODOS'){
                            $q->where('f.depto', $departamento);  
                        }
                    })
                    ->orderby('Folio')
                    ->orderby('Serie')
                    ->get();
                $arreglo = new Collection;
                foreach($data as $d){
                    $fechafactura = Carbon::parse($d->Fecha);
                    $fechahoy = Carbon::now();
                    if($fechafactura->year != $fechahoy->year || $fechafactura->month != $fechahoy->month){
                        $arreglo->push([
                            "Factura"=>$d->Factura,
                            "Serie"=>$d->Serie,
                            "Folio"=>$d->Folio,
                            "Esquema"=>$d->Esquema,
                            "Status"=>$d->Status,
                            "Cliente"=>$d->Cliente,
                            "Status"=>$d->Status,
                            "NombreCliente"=>$d->NombreCliente,
                            "Fecha"=>$d->Fecha,
                            "Depto"=>$d->Depto,
                            "FormaPago"=>$d->FormaPago,
                            "SubTotal"=>$d->SubTotal,
                            "Iva"=>$d->Iva,
                            "Total"=>$d->Total,
                            "Abonos"=>$d->Abonos,
                            "Descuentos"=>$d->Descuentos,
                            "Saldo"=>$d->Saldo,
                        ]);
                    }
                }
                //return Datatables::of($arreglo)->make(true);                    
                break;
            case "DETALLES":

                break;
        }
        return $arreglo;
    }
}
