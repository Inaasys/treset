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

class ReportesAntiguedadSaldosExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $saldomayor;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechacorte, $numerocliente, $claveserie, $departamento, $saldomayor, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "GENERAL":
                $this->campos_consulta = array("Cliente", "NombreCliente", "Facturado", "AbonosCXC", "DescuentosNotasCredito", "TotalPagos", "SaldoFacturado");
                break;
            case "DETALLES":
                $this->campos_consulta = array("Factura", "Fecha", "Plazo", "Cliente", "NombreCliente", "TotalFactura", "AbonosCXC", "DescuentosNotasCredito", "TotalPagos", "SaldoFacturado");
                break;
        }
        $this->fechacorte = $fechacorte;
        $this->numerocliente = $numerocliente;
        $this->claveserie = $claveserie;
        $this->departamento = $departamento;
        $this->saldomayor = $saldomayor;
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
        return 'Antiguedad Saldos'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechacorte = date($this->fechacorte);
        $numerocliente=$this->numerocliente;
        $claveserie=$this->claveserie;
        $departamento=$this->departamento;
        $saldomayor=$this->saldomayor;
        $reporte = $this->reporte;
        switch($reporte){
            case "GENERAL":
                if($fechacorte == date('Y-m-d')){
                    $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select("f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS Facturado"), 
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) as AbonosCXC"),
                                DB::raw("isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0) as DescuentosNotasCredito"),
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0) as TotalPagos"),
                                DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente group by cliente ),0)) as SaldoFacturado "))            
                    ->where('f.Status', '<>', 'BAJA')
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
                    ->where(function($q) use ($saldomayor) {
                        if($saldomayor > 0){
                            $q->where('c.Saldo', '>', 0.1);
                        }
                    })
                    ->orderby('f.Cliente')
                    ->groupby('f.Cliente', 'c.Nombre')
                    ->get();
                }else{
                    $data = DB::table('Facturas as f')
                    ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                    ->select("f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS Facturado"), 
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as AbonosCXC"),
                                DB::raw("isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as DescuentosNotasCredito"),
                                DB::raw("isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) as TotalPagos"),
                                DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0) + isnull((select sum(total) from [notas cliente detalles] where cliente = f.cliente and fecha <= '".$fechacorte."' group by cliente ),0)) as SaldoFacturado "))
                    ->where('f.Fecha', '<=', $fechacorte)
                    ->where('f.Status', '<>', 'BAJA')
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
                    ->where(function($q) use ($saldomayor) {
                        if($saldomayor > 0){
                            $q->where('c.Saldo', '>', 0.1);
                        }
                    })
                    ->where(function($q) use ($departamento) {
                        if($departamento != 'TODOS'){
                            $q->where('f.depto', $departamento);  
                        }
                    })
                    ->orderby('f.Cliente')
                    ->groupby('f.Cliente', 'c.Nombre')
                    ->get();
                }
                break;
            case "DETALLES":
                $data = DB::table('Facturas as f')
                ->leftjoin('Clientes as c', 'f.Cliente', '=', 'c.Numero')
                ->select("f.Factura", "f.Fecha", "f.Plazo", "f.Cliente AS Cliente", "c.Nombre AS NombreCliente", DB::raw("SUM(f.Total) AS TotalFactura"), 
                            DB::raw("isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) as AbonosCXC"),
                            //DB::raw("isnull((select sum(total) from [notas cliente detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) as DescuentosNotasCredito"),
                            DB::raw("isnull((SELECT sum(ncdoc.Descuento) AS DescuentosNotasCredito FROM dbo.[Notas Cliente Documentos] AS ncdoc LEFT OUTER JOIN dbo.[Notas Cliente Detalles] AS ncd ON ncd.Nota = ncdoc.Nota where ncdoc.Factura = f.Factura and ncd.Fecha <= '".$fechacorte."' group by ncdoc.Factura ),0) as DescuentosNotasCredito"),
                            DB::raw("isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) + isnull((SELECT sum(ncdoc.Descuento) AS DescuentosNotasCredito FROM dbo.[Notas Cliente Documentos] AS ncdoc LEFT OUTER JOIN dbo.[Notas Cliente Detalles] AS ncd ON ncd.Nota = ncdoc.Nota where ncdoc.Factura = f.Factura and ncd.Fecha <= '".$fechacorte."' group by ncdoc.Factura ),0) as TotalPagos"),
                            DB::raw("sum(f.total) - (isnull((select sum(abono) from [cxc detalles] where factura = f.factura and fecha <= '".$fechacorte."' ),0) + isnull((SELECT sum(ncdoc.Descuento) AS DescuentosNotasCredito FROM dbo.[Notas Cliente Documentos] AS ncdoc LEFT OUTER JOIN dbo.[Notas Cliente Detalles] AS ncd ON ncd.Nota = ncdoc.Nota where ncdoc.Factura = f.Factura and ncd.Fecha <= '".$fechacorte."' group by ncdoc.Factura ),0)) as SaldoFacturado "))
                ->where('f.Fecha', '<=', $fechacorte)
                ->where('f.Status', '<>', 'BAJA')
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
                ->where(function($q) use ($saldomayor) {
                    if($saldomayor > 0){
                        $q->where('f.Saldo', '>', 0.1);
                    }
                })
                ->where(function($q) use ($departamento) {
                    if($departamento != 'TODOS'){
                        $q->where('f.depto', $departamento);  
                    }
                })
                ->orderby('f.Fecha', 'DESC')
                ->groupby('f.Factura', 'f.Fecha', 'f.Plazo', 'f.Cliente', 'c.Nombre')
                ->get();
                break;
        }
        return $data;
    }
}
