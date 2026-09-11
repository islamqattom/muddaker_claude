<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceReportExport implements FromView, ShouldAutoSize, WithEvents
{
    public function __construct(
        public string $title,
        public array $headers,
        public array $rows,
        public array $stats = [],
        public ?string $subtitle = null,
    ) {}

    public function view(): View
    {
        return view('exports.report-table', [
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            'headers'  => $this->headers,
            'rows'     => $this->rows,
            'stats'    => $this->stats,
            'orient'   => 'portrait',
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
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
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

                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 === 0) {
                        $sheet->getStyle('A' . $i . ':' . $highestCol . $i)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                        ]);
                    }
                }
            },
        ];
    }
}
