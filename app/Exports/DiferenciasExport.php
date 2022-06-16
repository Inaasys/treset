<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Comprobante;
use Carbon\Carbon;
use DB;

class DiferenciasExport implements FromCollection, WithHeadings
{

    protected $decimales;

    public function __construct($decimalesSistema) {
        $this->decimales = $decimalesSistema;
    }

    public function headings(): array{
        return [
            'Version','Tipo','Fecha Emision','Serie','Folio','UUID','Total XML','Total Sistema','Diferencia'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $hoy = Carbon::now()->format('Y-m-d');
        $registros = collect([]);
        $comprobantes = Comprobante::whereBetween(DB::raw("CONVERT(varchar, FechaTimbrado, 23)"),[$hoy,$hoy])
        ->where('Tipo','I')
        ->orderBy('FechaTimbrado','DESC')->get();

        foreach ($comprobantes as $comprobante) {
            $factura = $comprobante->factura();
            $diferencia = number_format($comprobante->Total, $this->decimales, '.', '') - $factura->Total;
            if($diferencia > 0 ){
                $registros[] = collect([
                    "Version" => (string)$comprobante->Version.'   ',
                    "Tipo" => $comprobante->Comprobante,
                    "Fecha Emision"=>$comprobante->FechaTimbrado,
                    "Serie" => $comprobante->Serie,
                    "Folio" =>$comprobante->Folio,
                    "UUID" =>$comprobante->UUID,
                    "Total XML" => (float)number_format($comprobante->Total, 4, '.', '').' ',
                    "Total Sistema" => (float)number_format($factura->Total, 4, '.', '').' ',
                    "Diferencia" => (float)number_format($diferencia, 4, '.', '').' '
                ]);
            }
        }

        return $registros;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(100);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(100);

            },
        ];
    }
}
