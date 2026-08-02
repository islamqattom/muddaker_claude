<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LessonsTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithEvents
{
    public function headings(): array
    {
        return [
            'العنوان *',
            'المحتوى',
            'التاريخ (YYYY-MM-DD)',
            'وقت البداية (HH:MM)',
            'وقت النهاية (HH:MM)',
            'الحالة',
            'الملاحظات',
        ];
    }

    public function array(): array
    {
        return [
            ['الدرس الأول - مقدمة', 'حفظ سورة الفاتحة مع التجويد', date('Y-m-d'), '08:00', '09:00', 'pending', ''],
            ['الدرس الثاني - التطبيق', 'تطبيق عملي على ما سبق', date('Y-m-d', strtotime('+1 day')), '08:00', '09:00', 'pending', 'يحتاج متابعة'],
        ];
    }

    public function title(): string
    {
        return 'دروس المادة';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF182B48'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:G3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(22);
        }
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(30);

        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->setRightToLeft(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $validation = $sheet->getCell("F{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setFormula1('"pending,completed,cancelled"');
                }

                $sheet->getStyle('A1:G' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle('thin');
            },
        ];
    }
}
