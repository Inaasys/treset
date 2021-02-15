<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Jenssegers\Date\Date;
use Helpers;
use App\Cotizacion;
use App\CotizacionDetalle;

class FormatosExcelCotizacionExport implements FromView, WithTitle, WithDrawings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $cotizacion;

    public function __construct($cotizacion, $numeroinicialcelda, $numerofinalcelda, $numerodecimales, $empresa){
        $this->cotizacion = $cotizacion;
        $this->numeroinicialcelda = $numeroinicialcelda;
        $this->numerofinalcelda = $numerofinalcelda;
        $this->numerodecimales = $numerodecimales;
        $this->empresa = $empresa;
    }

    //titulo de la hoja de excel
    public function title(): string{
        return 'Cotizacion'.$this->cotizacion;
    }

    //Combinar celdas
    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('B'.$this->numeroinicialcelda.':B'.$this->numerofinalcelda);
                $event->sheet->getDelegate()->mergeCells('C'.$this->numeroinicialcelda.':C'.$this->numerofinalcelda);
                $event->sheet->getDelegate()->mergeCells('D'.$this->numeroinicialcelda.':D'.$this->numerofinalcelda);
                $event->sheet->getDelegate()->mergeCells('E'.$this->numeroinicialcelda.':E'.$this->numerofinalcelda);
                $event->sheet->getDelegate()->mergeCells('F'.$this->numeroinicialcelda.':F'.$this->numerofinalcelda);
            },
        ];
    }

    //cargar imagen en el reporte
    public function drawings(){
        $drawing = new Drawing();
        $drawing->setName($this->empresa->Nombre);
        $drawing->setDescription($this->empresa->Nombre);
        $drawing->setPath(public_path('logotipo_empresa/').$this->empresa->Logo);
        $drawing->setHeight(100);
        $drawing->setCoordinates('B2');
        return $drawing;
    }

    public function view(): View{
        $info_cotizacion = Cotizacion::where('cotizacion', $this->cotizacion)->first();
        $detallescotizacion = CotizacionDetalle::where('id_cotizacion', $info_cotizacion->id)->get();
        $detalles=array();
        foreach ($detallescotizacion as $detalle){
            $detalles[]=array(
                "fechadetalle"=>Carbon::parse($detalle->fecha)->toDateString(),
                "numero_parte"=>$detalle->numero_parte,
                "descripcion"=>$detalle->descripcion,
                "status_refaccion"=>$detalle->status_refaccion,
                "insumo"=>$detalle->insumo,
                "precio"=>number_format(Helpers::convertirvalorcorrecto($detalle->precio), $this->numerodecimales),
                "cantidad"=>number_format(Helpers::convertirvalorcorrecto($detalle->cantidad), $this->numerodecimales),
                "importe"=>number_format(Helpers::convertirvalorcorrecto($detalle->importe), $this->numerodecimales)
            );
        }
        $empresa = $this->empresa;
        $data = array(
            "info_cotizacion" => $info_cotizacion,
            "fechacotizacion"=>Carbon::parse($info_cotizacion->fecha)->toDateString(),
            "subtotal"=>number_format(Helpers::convertirvalorcorrecto($info_cotizacion->subtotal), $this->numerodecimales),
            "iva"=>number_format(Helpers::convertirvalorcorrecto($info_cotizacion->iva), $this->numerodecimales),
            "total"=>number_format(Helpers::convertirvalorcorrecto($info_cotizacion->total), $this->numerodecimales),
            "detallescotizacion" => $detallescotizacion,
            "detalles" => $detalles
        );
        return view('reportes.cotizaciones.formato_excel_cotizacion', compact('data','empresa'));
    }
}
