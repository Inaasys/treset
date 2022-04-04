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

class ReportesUnidadesServicioExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    private $campos_consulta;
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $numeroclientefacturara;
    private $numeroclientedelcliente;
    private $numerovin;
    private $tipoorden;
    private $tipounidad;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;
    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroclientefacturara, $numeroclientedelcliente, $numerovin, $tipoorden, $tipounidad, $status, $reporte, $numerodecimales, $empresa){    
        switch($reporte){
            case "NORMAL":
                $this->campos_consulta = array('Orden', 'Cliente', 'NombreCliente', 'Fecha', 'Entrega', 'Facturada', 'Status', 'Vin', 'Codigo', 'Descripcion', 'Cantidad', 'Precio', 'Dcto', 'Descuento', 'SubTotal', 'Iva', 'Total', 'Costo', 'Utilidad', 'Cargo', 'Compra', 'Traspaso', 'Kilometros', 'Economico', 'Año', 'Modelo', 'Marca', 'Tecnico1', 'NombreTecnico1', 'Horas1', 'Tecnico2', 'NombreTecnico2', 'Horas2', 'Tecnico3', 'NombreTecnico3', 'Horas3', 'Tecnico4', 'NombreTecnico4', 'Horas4', 'Falla', 'Causa', 'Correccion');
                break;
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroclientefacturara = $numeroclientefacturara;
        $this->numeroclientedelcliente = $numeroclientedelcliente;
        $this->numerovin = $numerovin;
        $this->tipoorden = $tipoorden;
        $this->tipounidad = $tipounidad;
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
        return 'Unidades Servicio-'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $numeroclientefacturara=$this->numeroclientefacturara;
        $numeroclientedelcliente=$this->numeroclientedelcliente;
        $numerovin=$this->numerovin;
        $tipoorden=$this->tipoorden;
        $tipounidad=$this->tipounidad;
        $status=$this->status;
        $reporte = $this->reporte;
        switch($reporte){
            case "NORMAL":
                $data = DB::table('Ordenes de Trabajo as ot')
                            ->join('Ordenes de Trabajo Detalles as otd', 'ot.Orden', '=', 'otd.Orden')
                            ->join('Clientes as c', 'ot.Cliente', '=', 'c.Numero')
                            ->select('otd.Orden', 'ot.Cliente', 'c.Nombre as NombreCliente', 'ot.Fecha', 'ot.Entrega', 'ot.Facturada', 'ot.Status', 'ot.Vin', 'otd.Codigo', DB::raw("case otd.departamento+otd.cargo when 'SERVICIO'+'SERVICIO' then (select top 1 servicio from servicios where codigo = otd.codigo) else (select top 1 producto from productos where codigo = otd.codigo) end as Descripcion"), 'otd.Cantidad', 'otd.Precio', 'otd.Dcto', 'otd.Descuento', 'otd.SubTotal', 'otd.Iva', 'otd.Total', 'otd.Costo', 'otd.Utilidad', 'otd.Cargo', 'otd.Compra', 'otd.Traspaso', 'ot.Kilometros', 'ot.Economico', 'ot.Año', 'ot.Modelo', 'ot.Marca', 'otd.Tecnico1', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico1) as NombreTecnico1"), 'otd.Horas1', 'otd.Tecnico2', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico2) as NombreTecnico2"), 'otd.Horas2', 'otd.Tecnico3', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico3) as NombreTecnico3"), 'otd.Horas3', 'otd.Tecnico4', DB::raw("(select top 1 nombre from tecnicos where numero = otd.tecnico4) as NombreTecnico4"), 'otd.Horas4', 'ot.Falla', 'ot.Causa', 'ot.Correccion')
                            ->whereDate('ot.Fecha', '>=', $fechainicio)->whereDate('ot.Fecha', '<=', $fechaterminacion)
                            ->where(function($q) use ($numeroclientefacturara) {
                                if($numeroclientefacturara != ""){
                                    $q->where('ot.Cliente', $numeroclientefacturara);
                                }
                            })
                            ->where(function($q) use ($numeroclientedelcliente) {
                                if($numeroclientedelcliente != ""){
                                    $q->where('ot.DelCliente', $numeroclientedelcliente);
                                }
                            })
                            ->where(function($q) use ($numerovin) {
                                if($numerovin != 'TODOS'){
                                    $q->where('ot.Vin', 'like', '%' . $numerovin . '%');
                                }
                            })
                            ->where(function($q) use ($tipoorden) {
                                if($tipoorden != 'TODOS'){
                                    $q->where('ot.Tipo', $tipoorden);
                                }
                            })
                            ->where(function($q) use ($tipounidad) {
                                if($tipounidad != 'TODOS'){
                                    $q->where('ot.Unidad', $tipounidad);
                                }
                            })
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    if($status == 'ABIERTAS'){
                                        $q->where('ot.Status', 'ABIERTA');
                                    }elseif($status == 'FACTURADAS'){
                                        $q->where('ot.Status', '<>', 'ABIERTA')->where('ot.Status', '<>', 'BAJA');
                                    }elseif($status == 'BAJA'){
                                        $q->where('ot.Status', 'BAJA');            
                                    }
                                }
                            })
                            ->orderby('ot.Serie', 'ASC')
                            ->orderby('ot.Folio', 'ASC')
                            ->orderby('otd.Item', 'ASC')
                            ->get();
                break;
            }
        return $data;
    }
}
