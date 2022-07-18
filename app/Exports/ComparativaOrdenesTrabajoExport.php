<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\OrdenTrabajo;


class ComparativaOrdenesTrabajoExport implements WithColumnFormatting,WithEvents,FromCollection, WithHeadings,WithPreCalculateFormulas,WithColumnWidths
{

    protected $decimales;
    protected $listado;
    private $currentRow = 2;

    public function __construct($decimalesSistema, $listado) {
        $this->decimales = $decimalesSistema;
        $this->listado = $listado;
    }
    /**
     * Coloca los encabezados del archivo
     */
    public function headings(): array{
        return [
            'OT','PEDIDO','ECONOMICO','CANTIDAD','PRECIO','SUBTOTAL','IVA %','IVA $','TOTAL'
        ];
    }
    /**
     * Da formato a las columnas
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * Da tamaño a las filas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12.5,
            'B' => 12.5,
            'C' => 14,
            'D' => 12.5,
            'E' => 14,
            'F' => 12.5,
            'G' => 14,
            'H' => 12.5,
            'I' => 12.5,
            'J' => 12.5,
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $registros = collect([]);
        $importePartida = 0;
        $impuesto = 0;
        $total = 0;
        $sumaImportes = 0;
        $ivaOT = 0;
        $totalOT = 0;
        $totaLHoras = 0;
        foreach ($this->listado as $orden) {
            $importePartida = 0;
            $impuesto = 0;
            $total = 0;
            $sumaImportes = 0;
            $ivaOT = 0;
            $totalOT = 0;
            $totaLHoras = 0;
            foreach ($orden->detalles->sortBy('Partida') as $detalle) {
                $importePartida = $detalle->Cantidad * $detalle->Precio;
                $impuesto = $importePartida * ($detalle->Impuesto / 100);
                $total = $importePartida + $impuesto;
                $sumaImportes += $importePartida;
                $totaLHoras += $detalle->Cantidad;
                $registros[] = collect([
                    "OT" => $detalle->Orden,
                    "PEDIDO" => $orden->Pedido,
                    "ECONOMICO" => $orden->Economico,
                    "CANTIDAD" => $detalle->Cantidad,
                    "PRECIO" => $detalle->Precio,
                    "SUBTOTAL" => number_format($importePartida, $this->decimales, '.', ''),
                    "IVA %" => $detalle->Impuesto / 100,
                    "IVA $" => number_format($impuesto, $this->decimales, '.', ''),
                    "TOTAL" => number_format($total, $this->decimales, '.', ''),
                ]);
            }
            $sumaImportes = number_format(round($sumaImportes,2), $this->decimales, '.', '');
            $ivaOT = number_format(round($sumaImportes,2), $this->decimales, '.', '') * 0.16;
            $ivaOT = number_format(round($ivaOT,2), $this->decimales, '.', '');
            $totalOT = $totalTotal = number_format(round($sumaImportes, 2), $this->decimales, '.', '') + number_format(round($ivaOT, 2), $this->decimales, '.', '');
            $registros[] = collect([
                "OT" => $orden->Orden,
                "PEDIDO" => "",
                "ECONOMICO" => "Total Cantidad",
                "CANTIDAD" => $totaLHoras,
                "PRECIO" => "Subtotal Total",
                "SUBTOTAL" => $sumaImportes,
                "IVA %" => "IVA Total",
                "IVA $" => $ivaOT,
                "TOTAL" => $totalOT,
            ]);
            $registros[] = collect([
                "OT" => "",
                "PEDIDO" => "",
                "ECONOMICO" => "",
                "CANTIDAD" => "",
                "PRECIO" => "",
                "SUBTOTAL" => "",
                "IVA %" => "",
                "IVA $" => "",
                "TOTAL" => "",
            ]);
        }
        return $registros;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:J1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }

}
