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

class ReportesRelacionCompraExport implements FromCollection,WithHeadings,WithTitle,WithColumnWidths
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
    private $movimiento;
    private $status;
    private $reporte;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $numeroproveedor, $numeroalmacen, $tipo, $movimiento, $status, $reporte, $numerodecimales, $empresa){
        if($reporte == 'RELACION'){
            $this->campos_consulta = array('Compra', 'Proveedor', 'Nombre', 'Fecha', 'Plazo', 'Vence', 'Remision', 'Factura', 'Movimiento', 'Almacen', 'Tipo', 'Importe', 'Descuento', 'SubTotal', 'Iva', 'Total', 'Abonos', 'Descuentos', 'Saldo', 'Obs', 'Status', 'MotivoBaja', 'Usuario', 'Rfc', 'Calle', 'NoExterior', 'Colonia', 'Municipio', 'Estado', 'CodigoPostal', 'Contacto', 'Telefonos', 'Email1');
        }else if($reporte == 'DETALLES'){
            $this->campos_consulta = array('Compra', 'Proveedor', 'Nombre', 'Fecha', 'Plazo', 'Vence', 'Remision', 'Factura', 'Movimiento', 'Almacen', 'Tipo', 'Codigo', 'Descripcion', 'Unidad', 'Cantidad', 'Precio', 'Importe', 'Descuento', 'SubTotal', 'Iva', 'Total', 'ObsCompra', 'ObsDetalle', 'Status', 'MotivoBaja', 'Usuario', 'Rfc', 'Calle', 'NoExterior', 'Colonia', 'Municipio', 'Estado', 'CodigoPostal', 'Contacto', 'Telefonos', 'Email1');
        }else{
            $this->campos_consulta = array('Numero', 'Nombre', 'Totalc', 'Rfc', 'Calle', 'NoExterior', 'Colonia', 'Municipio', 'Estado', 'CodigoPostal', 'Contacto', 'Telefonos', 'Email1');
        }
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->numeroproveedor = $numeroproveedor;
        $this->numeroalmacen = $numeroalmacen;
        $this->tipo = $tipo;
        $this->movimiento = $movimiento;
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
        return 'Relación Compras-'.$this->reporte;
    }

    public function headings(): array{
        return $this->campos_consulta;
    }
    
    public function collection(){
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $numeroproveedor = $this->numeroproveedor;
        $numeroalmacen = $this->numeroalmacen;
        $tipo = $this->tipo;
        $movimiento = $this->movimiento;
        $status = $this->status;
        $campos_consulta = $this->campos_consulta;
        if($reporte == "GENERAL"){
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', DB::raw("c.Fecha+c.Plazo as Vence"), 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'c.Importe', 'c.Descuento', 'c.SubTotal', 'c.Iva', 'c.Total', 'c.Abonos', 'c.Descuentos', 'c.Saldo', 'c.Obs', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('c.Proveedor', $numeroproveedor);
                }
            })
            ->where(function($q) use ($numeroalmacen) {
                if($numeroalmacen != ""){
                    $q->whereIn('c.Almacen', array($numeroalmacen));
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('c.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($movimiento) {
                if($movimiento != 'TODOS'){
                    $q->where('c.Movimiento', 'LIKE', '%'.$movimiento.'%');
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('c.Status', $status);
                }
            })
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
            ->get();
        }else if($reporte == "DETALLES"){
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->leftjoin('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', DB::raw("c.Fecha+c.Plazo as Vence"), 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'cd.Codigo', 'cd.Descripcion', 'cd.Unidad', 'cd.Cantidad', 'cd.Precio', 'cd.Importe', 'cd.Descuento', 'cd.SubTotal', 'cd.Iva', 'cd.Total', 'c.Obs AS ObsCompra', 'cd.Obs As ObsDetalle', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('c.Proveedor', $numeroproveedor);
                }
            })
            ->where(function($q) use ($numeroalmacen) {
                if($numeroalmacen != ""){
                    $q->whereIn('c.Almacen', array($numeroalmacen));
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('c.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($movimiento) {
                if($movimiento != 'TODOS'){
                    $q->where('c.Movimiento', 'LIKE', '%'.$movimiento.'%');
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('c.Status', $status);
                }
            })
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
            ->get();
        }else{
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('p.Numero', 'p.Nombre', DB::raw("FORMAT(SUM(c.Total), 'N6') as Totalc"), 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->where(function($q) use ($numeroproveedor) {
                if($numeroproveedor != ""){
                    $q->where('p.Numero', $numeroproveedor);
                }
            })
            ->where(function($q) use ($numeroalmacen) {
                if($numeroalmacen != ""){
                    $q->whereIn('c.Almacen', array($numeroalmacen));
                }
            })
            ->where(function($q) use ($tipo) {
                if($tipo != 'TODOS'){
                    $q->where('c.Tipo', $tipo);
                }
            })
            ->where(function($q) use ($movimiento) {
                if($movimiento != 'TODOS'){
                    $q->where('c.Movimiento', 'LIKE', '%'.$movimiento.'%');
                }
            })
            ->where(function($q) use ($status) {
                if($status != 'TODOS'){
                    $q->where('c.Status', $status);
                }
            })
            ->where('c.Status', '<>', 'BAJA')
            ->groupby('p.Numero', 'p.Nombre', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->orderby(DB::raw("SUM(c.Total)"), 'DESC')
            ->get();
        }
        return $sql;
    }

    /*
    public function view(): View{
        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $reporte = $this->reporte;
        $campos_consulta = $this->campos_consulta;
        if($reporte == "RELACION"){
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'c.Importe', 'c.Descuento', 'c.SubTotal', 'c.Iva', 'c.Total', 'c.Abonos', 'c.Descuentos', 'c.Saldo', 'c.Obs', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
            ->get();
            if($this->numeroproveedor != ""){
                $sql = $sql->where('Proveedor', $this->numeroproveedor);
            }
            if($this->numeroalmacen != ""){
                $sql = $sql->where('Almacen', $this->numeroalmacen);
            }
            if($this->tipo != 'TODOS'){
                $sql = $sql->where('Tipo', $this->tipo);
            }
            if($this->movimiento != 'TODOS'){
                $sql = $sql->where('Movimiento', $this->movimiento);
            }
            if($this->status != 'TODOS'){
                $sql = $sql->where('Status', $this->status);
            }
            foreach ($sql as $s){
                $data[]=array(
                    "Compra"=>$s->Compra,
                    "Proveedor"=>$s->Proveedor,
                    "Nombre"=>$s->Nombre,
                    "Fecha"=>$s->Fecha,
                    "Plazo"=>$s->Plazo,
                    "Vence"=>Helpers::fecha_espanol(Carbon::parse($s->Fecha)->addDays($s->Plazo)->toDateTimeString()),
                    "Remision"=>$s->Remision,
                    "Factura"=>$s->Factura,
                    "Movimiento"=>$s->Movimiento,
                    "Almacen"=>$s->Almacen,
                    "Tipo"=>$s->Tipo,
                    "Importe"=>number_format(Helpers::convertirvalorcorrecto($s->Importe), $this->numerodecimales),
                    "Descuento"=>number_format(Helpers::convertirvalorcorrecto($s->Descuento), $this->numerodecimales),
                    "SubTotal"=>number_format(Helpers::convertirvalorcorrecto($s->SubTotal), $this->numerodecimales),
                    "Iva"=>number_format(Helpers::convertirvalorcorrecto($s->Iva), $this->numerodecimales),
                    "Total"=>number_format(Helpers::convertirvalorcorrecto($s->Total), $this->numerodecimales),
                    "Abonos"=>number_format(Helpers::convertirvalorcorrecto($s->Abonos), $this->numerodecimales),
                    "Descuentos"=>number_format(Helpers::convertirvalorcorrecto($s->Descuentos), $this->numerodecimales),
                    "Saldo"=>number_format(Helpers::convertirvalorcorrecto($s->Saldo), $this->numerodecimales),
                    "Obs"=>$s->Obs,
                    "Status"=>$s->Status,
                    "MotivoBaja"=>$s->MotivoBaja,
                    "Usuario"=>$s->Usuario,
                    "Rfc"=>$s->Rfc,
                    "Calle"=>$s->Calle,
                    "NoExterior"=>$s->NoExterior,
                    "Colonia"=>$s->Colonia,
                    "Municipio"=>$s->Municipio,
                    "Estado"=>$s->Estado,
                    "CodigoPostal"=>$s->CodigoPostal,
                    "Contacto"=>$s->Contacto,
                    "Telefonos"=>$s->Telefonos,
                    "Email1"=>$s->Email1
                );
            }
        }else if($reporte == "DETALLES"){
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->leftjoin('Compras Detalles as cd', 'c.Compra', '=', 'cd.Compra')
            ->select('c.Compra', 'c.Proveedor', 'p.Nombre', 'c.Fecha', 'c.Plazo', 'c.Remision', 'c.Factura', 'c.Movimiento', 'c.Almacen', 'c.Tipo', 'cd.Codigo', 'cd.Descripcion', 'cd.Unidad', 'cd.Cantidad', 'cd.Precio', 'cd.Importe', 'cd.Descuento', 'cd.SubTotal', 'cd.Iva', 'cd.Total', 'c.Obs AS ObsCompra', 'cd.Obs As ObsDetalle', 'c.Status', 'c.MotivoBaja', 'c.Usuario', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->orderby('c.Serie', 'ASC')
            ->orderby('c.Folio', 'ASC')
            ->get();
            if($this->numeroproveedor != ""){
                $sql = $sql->where('Proveedor', $this->numeroproveedor);
            }
            if($this->numeroalmacen != ""){
                $sql = $sql->where('Almacen', $this->numeroalmacen);
            }
            if($this->tipo != 'TODOS'){
                $sql = $sql->where('Tipo', $this->tipo);
            }
            if($this->movimiento != 'TODOS'){
                $sql = $sql->where('Movimiento', $this->movimiento);
            }
            if($this->status != 'TODOS'){
                $sql = $sql->where('Status', $this->status);
            }
            foreach ($sql as $s){
                $data[]=array(
                    "Compra"=>$s->Compra,
                    "Proveedor"=>$s->Proveedor,
                    "Nombre"=>$s->Nombre,
                    "Fecha"=>$s->Fecha,
                    "Plazo"=>$s->Plazo,
                    "Vence"=>Helpers::fecha_espanol(Carbon::parse($s->Fecha)->addDays($s->Plazo)->toDateTimeString()),
                    "Remision"=>$s->Remision,
                    "Factura"=>$s->Factura,
                    "Movimiento"=>$s->Movimiento,
                    "Almacen"=>$s->Almacen,
                    "Tipo"=>$s->Tipo,
                    "Codigo"=>$s->Codigo,
                    "Descripcion"=>$s->Descripcion,
                    "Unidad"=>$s->Unidad,
                    "Cantidad"=>$s->Cantidad,
                    "Precio"=>number_format(Helpers::convertirvalorcorrecto($s->Precio), $this->numerodecimales),
                    "Importe"=>number_format(Helpers::convertirvalorcorrecto($s->Importe), $this->numerodecimales),
                    "Descuento"=>number_format(Helpers::convertirvalorcorrecto($s->Descuento), $this->numerodecimales),
                    "SubTotal"=>number_format(Helpers::convertirvalorcorrecto($s->SubTotal), $this->numerodecimales),
                    "Iva"=>number_format(Helpers::convertirvalorcorrecto($s->Iva), $this->numerodecimales),
                    "Total"=>number_format(Helpers::convertirvalorcorrecto($s->Total), $this->numerodecimales),
                    "ObsCompra"=>$s->ObsCompra,
                    "ObsDetalle"=>$s->ObsDetalle,
                    "Status"=>$s->Status,
                    "MotivoBaja"=>$s->MotivoBaja,
                    "Usuario"=>$s->Usuario,
                    "Rfc"=>$s->Rfc,
                    "Calle"=>$s->Calle,
                    "NoExterior"=>$s->NoExterior,
                    "Colonia"=>$s->Colonia,
                    "Municipio"=>$s->Municipio,
                    "Estado"=>$s->Estado,
                    "CodigoPostal"=>$s->CodigoPostal,
                    "Contacto"=>$s->Contacto,
                    "Telefonos"=>$s->Telefonos,
                    "Email1"=>$s->Email1
                );
            }
        }else{
            $sql = DB::table('Compras as c')
            ->leftjoin('Proveedores as p', 'c.Proveedor', '=', 'p.Numero')
            ->select('p.Numero', 'p.Nombre', DB::raw("SUM(c.Total) as Totalc"), 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->whereBetween('c.Fecha', [$fechainicio, $fechaterminacion])
            ->where('c.Status', '<>', 'BAJA')
            ->groupby('p.Numero', 'p.Nombre', 'p.Rfc', 'p.Calle', 'p.NoExterior', 'p.Colonia', 'p.Municipio', 'p.Estado', 'p.CodigoPostal', 'p.Contacto', 'p.Telefonos', 'p.Email1')
            ->orderby(DB::raw("SUM(c.Total)"), 'DESC')
            ->get();
            if($this->numeroproveedor != ""){
                $sql = $sql->where('Numero', $this->numeroproveedor);
            }
            if($this->numeroalmacen != ""){
                $sql = $sql->where('Almacen', $this->numeroalmacen);
            }
            if($this->tipo != 'TODOS'){
                $sql = $sql->where('Tipo', $this->tipo);
            }
            if($this->movimiento != 'TODOS'){
                $sql = $sql->where('Movimiento', $this->movimiento);
            }
            if($this->status != 'TODOS'){
                $sql = $sql->where('Status', $this->status);
            }
            foreach ($sql as $s){
                $data[]=array(
                    "Numero"=>$s->Numero,
                    "Nombre"=>$s->Nombre,
                    "Totalc"=>number_format(Helpers::convertirvalorcorrecto($s->Totalc), $this->numerodecimales),
                    "Rfc"=>$s->Rfc,
                    "Calle"=>$s->Calle,
                    "NoExterior"=>$s->NoExterior,
                    "Colonia"=>$s->Colonia,
                    "Municipio"=>$s->Municipio,
                    "Estado"=>$s->Estado,
                    "CodigoPostal"=>$s->CodigoPostal,
                    "Contacto"=>$s->Contacto,
                    "Telefonos"=>$s->Telefonos,
                    "Email1"=>$s->Email1
                );
            }
        }
        return view('reportes.compras.formato_reporte_relacion_compras', compact('data','reporte','campos_consulta'));
    }
    */
    
}
