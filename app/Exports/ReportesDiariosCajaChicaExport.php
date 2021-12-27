<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\Compra;
use App\Proveedor;

class ReportesDiariosCajaChicaExport implements FromView,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $fechainicialreporte;
    private $fechafinalreporte;
    private $statuscompra;
    private $string_compras;
    private $numerodecimales;
    private $empresa;

    public function __construct($fechainicialreporte, $fechafinalreporte, $statuscompra, $string_compras, $numerodecimales, $empresa){
        $this->fechainicialreporte = $fechainicialreporte;
        $this->fechafinalreporte = $fechafinalreporte;
        $this->statuscompra = $statuscompra;
        $this->string_compras = $string_compras;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Caja Chica';
    }

    public function view(): View{
        $compras = array();
        foreach(explode(",", $this->string_compras) as $compra){
            array_push($compras, $compra);
        }


        $fechainicio = date($this->fechainicialreporte);
        $fechaterminacion = date($this->fechafinalreporte);
        $status = $this->statuscompra;

        $fechahoy = Carbon::parse($this->fechafinalreporte);//fecha de la que se realizar el reporte
        $compras = Compra::whereDate('Fecha', '>=', $fechainicio)->whereDate('Fecha', '<=', $fechaterminacion)
                            ->whereIn('Compra', $compras)
                            ->where('Tipo', 'CAJA CHICA')
                            ->where(function($q) use ($status) {
                                if($status != 'TODOS'){
                                    $q->where('Status', $status);
                                }
                            })
                            ->get();
        $data=array();
        $sumasubtotal = 0;
        $sumaiva = 0;
        $sumaivaretencion = 0;
        $sumatotal = 0;
        foreach ($compras as $c){
            $sumasubtotal = $sumasubtotal + $c->SubTotal;
            $sumaiva = $sumaiva + $c->Iva;
            $sumaivaretencion = $sumaivaretencion + $c->IvaRetencion;
            $sumatotal = $sumatotal + $c->Total;
            $proveedor = Proveedor::where('Numero', $c->Proveedor)->first();
            $data[]=array(
                "compra"=>$c,
                "fechacompra"=>Carbon::parse($c->Fecha)->toDateString(),
                "movimientocompra"=>$c->Compra,
                "proveedor"=>$c->EmisorNombre,
                "UUID"=>$c->UUID,
                "conceptopago"=>"",
                "observacionescompra"=>$c->Obs,
                "subtotal"=>number_format(Helpers::convertirvalorcorrecto($c->SubTotal), $this->numerodecimales),
                "iva"=>number_format(Helpers::convertirvalorcorrecto($c->Iva), $this->numerodecimales),
                "ivaretencion"=>number_format(Helpers::convertirvalorcorrecto($c->IvaRetencion), $this->numerodecimales),
                "imphospedaje"=>"",
                "total"=>number_format(Helpers::convertirvalorcorrecto($c->Total), $this->numerodecimales),
                "depto"=>"",
                "sumasubtotal"=>number_format(Helpers::convertirvalorcorrecto($sumasubtotal), $this->numerodecimales),
                "sumaiva"=>number_format(Helpers::convertirvalorcorrecto($sumaiva), $this->numerodecimales),
                "sumaivaretencion"=>number_format(Helpers::convertirvalorcorrecto($sumaivaretencion), $this->numerodecimales),
                "sumatotal"=>number_format(Helpers::convertirvalorcorrecto($sumatotal), $this->numerodecimales)
            );
        }
        $empresa = $this->empresa;
        $fechahoy = Carbon::now()->toDateString();
        return view('reportes.compras.formato_excel_caja_chica', compact('data','empresa','fechahoy'));
    }
}
