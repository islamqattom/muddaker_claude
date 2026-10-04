<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HimmaReportExport implements FromView, ShouldAutoSize, WithEvents
{
    public function __construct(
        public string $title,
        public array $stats = [],
        public array $tables = [],
        public ?string $subtitle = null,
    ) {}

    public function view(): View
    {
        return view('exports.himma-report', [
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            'stats'    => $this->stats,
            'tables'   => $this->tables,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF182B48']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                if ($highestRow >= 2) {
                    $sheet->getStyle('A2:' . $highestCol . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => [
                            'allBorders' => ['borderStyle' => 'thin', 'color' => ['argb' => 'FFEEEEEE']],
                        ],
                    ]);
                }
            },
        ];
    }
}
