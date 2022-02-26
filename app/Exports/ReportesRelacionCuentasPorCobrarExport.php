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
use App\Remision;
use App\RemisionDetalle;
use App\Cliente;
use App\Agente;
use DB;

class ReportesRelacionCuentasPorCobrarExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $numerobanco;
    private $claveformapago;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numerocliente, $numeroagente, $numerobanco, $claveformapago, $reporte, $numerodecimales, $empresa){
        switch($reporte){
            case "AGRUPARxCLIENTES":
                $this->campos_consulta = array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
                break;
            case "AGRUPARxAGENTES":
                $this->campos_consulta = array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
                break;
            case "AGRUPARxFORMADEPAGO":
                $this->campos_consulta = array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
                break;
            case "AGRUPARxBANCO":
                $this->campos_consulta = array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Factura", "Agente", "NombreAgente", "Total", "Abono", "Saldo", "SubTotal", "Utilidad", "Anotacion", "MotivoBaja", "Status");
                break;
            case "RELACIONDEPAGOS":
                $this->campos_consulta = array("Pago", "Fecha", "Cliente", "FormaPago", "Banco", "Abono", "Anotacion", "MotivoBaja", "Status");
                break;
            case "COMISIONAAGENTES":
                $this->campos_consulta = array("Factura", "FechaFactura", "NombreCliente", "MontoFactura", "NombreAgente", "Pago", "FechaPago", "Dias", "Abono", "Comision", "ComisionPesos", "FormaPago");
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numerocliente = $numerocliente;
        $this->numeroagente = $numeroagente;
        $this->numerobanco = $numerobanco;
        $this->claveformapago = $claveformapago;
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
            'AJ' => 15,
            'AK' => 15,
            'AL' => 15,
            'AM' => 15,
            'AN' => 15,
            'AO' => 15,
            'AP' => 15,
            'AQ' => 15,
            'AR' => 15,
            'AS' => 15,
            'AT' => 15,
            'AU' => 15,
            'AV' => 15,
            'AW' => 15,
            'AX' => 15,
            'AY' => 15,
            'AZ' => 15          
        ];
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Relación Cuentas Por Cobrar'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }

    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numerocliente=$this->numerocliente;
        $numeroagente=$this->numeroagente;
        $numerobanco=$this->numerobanco;
        $claveformapago=$this->claveformapago;
        switch($reporte){
            case "AGRUPARxCLIENTES":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "AGRUPARxAGENTES":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "AGRUPARxFORMADEPAGO":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "AGRUPARxBANCO":
                $data = DB::table('CxC as cxc')
                ->join('CxC Detalles as cxcd', 'cxc.Pago', '=', 'cxcd.Pago')
                ->join('Facturas as f', 'cxcd.Factura', '=', 'f.Factura')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('Agentes as a', 'c.Agente', '=', 'a.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxcd.Pago', 'cxcd.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxcd.Factura", 'a.Numero as Agente', 'a.Nombre as NombreAgente', 'f.Total', 'cxcd.Abono', 'f.Saldo', 'f.SubTotal', 'f.Utilidad', 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "RELACIONDEPAGOS":
                $data = DB::table('CxC as cxc')
                ->join('Clientes as c', 'cxc.Cliente', '=', 'c.Numero')
                ->join('c_FormaPago as fp', 'cxc.FormaPago', '=', 'fp.Clave')
                ->join('Bancos as b', 'cxc.Banco', '=', 'b.Numero')
                ->select('cxc.Pago', 'cxc.Fecha', 'c.Nombre as Cliente', 'fp.Nombre AS FormaPago', 'b.Nombre as Banco', "cxc.Abono", 'cxc.Anotacion', 'cxc.MotivoBaja', 'cxc.Status')
                ->whereDate('cxc.Fecha', '>=', $fechainicio)->whereDate('cxc.Fecha', '<=', $fechaterminacion)
                ->where(function($q) use ($numerocliente) {
                    if($numerocliente != ""){
                        $q->where('c.Numero', $numerocliente);
                    }
                })
                ->where(function($q) use ($numeroagente) {
                    if($numeroagente != ""){
                        $q->where('f.Agente', $numeroagente);
                    }
                })
                ->where(function($q) use ($numerobanco) {
                    if($numerobanco != ""){
                        $q->where('cxc.Banco', $numerobanco);
                    }
                })
                ->where(function($q) use ($claveformapago) {
                    if($claveformapago != ""){
                        $q->where('cxc.FormaPago', $claveformapago);
                    }
                })
                ->orderby('cxc.Serie')
                ->orderby('cxc.Folio')
                ->get();
                break;
            case "COMISIONAAGENTES":
                break;
        }
        return $data;
    }
}
