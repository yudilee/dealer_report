<?php

namespace App\Exports;

use App\Services\ReportCalculationService;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WeeklyReportExport implements WithEvents, WithTitle
{
    private int $year;
    private int $month;

    public function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function title(): string
    {
        return 'WDR - AFTERSALES';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $service = new ReportCalculationService();
                $report = $service->calculateWDR($this->year, $this->month);

                $this->buildSheet($sheet, $report);
            },
        ];
    }

    private function buildSheet($sheet, array $report): void
    {
        $periods = $report['periods'];
        $numFormat = '#,##0';

        // === Title ===
        $sheet->setCellValue('A1', 'WEEKLY DEALER REPORT - AFTERSALES');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year)));

        // === Period Headers (row 4-5) ===
        $sheet->setCellValue('A4', '#');
        $sheet->setCellValue('B4', 'Description');
        $sheet->setCellValue('C4', 'Unit');
        $cols = ['D', 'F', 'H', 'J']; // 2 cols per period (Amount, %)
        foreach ($periods as $i => $label) {
            $col = $cols[$i];
            $nextCol = chr(ord($col) + 1);
            $sheet->setCellValue($col . '4', $label);
            $sheet->mergeCells($col . '4:' . $nextCol . '4');
            $sheet->setCellValue($col . '5', 'Amount');
            $sheet->setCellValue($nextCol . '5', '%');
        }

        // Header style
        $headerRange = 'A4:K5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a2744']],
        ]);
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('f1f5f9');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(8);
        foreach (['D', 'F', 'H', 'J'] as $c) {
            $sheet->getColumnDimension($c)->setWidth(18);
            $sheet->getColumnDimension(chr(ord($c) + 1))->setWidth(8);
        }

        $row = 6;

        // === Sections ===
        $sections = [
            ['title' => 'WORKSHOP - PC', 'data' => $report['workshop_pc'], 'type' => 'workshop'],
            ['title' => 'WORKSHOP - BODY SHOP', 'data' => $report['bodyshop'], 'type' => 'workshop'],
            ['title' => 'WORKSHOP - CV', 'data' => $report['workshop_cv'], 'type' => 'workshop'],
            ['title' => 'PARTS - PC', 'data' => $report['parts_pc'], 'type' => 'parts'],
            ['title' => 'PARTS - CV', 'data' => $report['parts_cv'], 'type' => 'parts'],
        ];

        foreach ($sections as $section) {
            // Section header
            $sheet->setCellValue("B{$row}", $section['title']);
            $sheet->mergeCells("A{$row}:K{$row}");
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0CECE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;

            if ($section['type'] === 'workshop') {
                $row = $this->writeWorkshopRows($sheet, $row, $section['data'], $cols, $numFormat);
            } else {
                $row = $this->writePartsRows($sheet, $row, $section['data'], $cols, $numFormat);
            }
        }

        // Freeze panes
        $sheet->freezePane('D6');
    }

    private function writeWorkshopRows($sheet, int $row, array $data, array $cols, string $numFormat): int
    {
        $metrics = [
            ['label' => 'Pendapatan Labor', 'key' => 'pendapatan_labor', 'unit' => 'Rp', 'style' => 'revenue'],
            ['label' => 'Total Unit', 'key' => 'total_unit', 'unit' => 'Unit', 'style' => 'metric'],
            ['label' => 'Avg. Throughput/Hari', 'key' => 'avg_throughput_per_day', 'unit' => 'Unit', 'style' => 'metric', 'decimal' => true],
            ['label' => 'Avg. Jam Jual/Unit', 'key' => 'avg_jam_jual_per_unit', 'unit' => 'Jam', 'style' => 'metric', 'decimal' => true],
            ['label' => 'Avg. Labor/Jam', 'key' => 'avg_labor_per_jam', 'unit' => 'Rp', 'style' => 'metric'],
            ['label' => 'Avg. Spend/Unit', 'key' => 'avg_spend_per_unit', 'unit' => 'Rp', 'style' => 'metric'],
            ['label' => 'Pendapatan Sublet', 'key' => 'pendapatan_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'HPP Sublet', 'key' => 'hpp_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'Gross Margin Sublet', 'key' => 'gross_margin_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'Pendapatan Sundry', 'key' => 'pendapatan_sundry', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'Total Gross Margin', 'key' => 'gross_margin', 'unit' => 'Rp', 'style' => 'total'],
        ];

        foreach ($metrics as $metric) {
            $sheet->setCellValue("B{$row}", $metric['label']);
            $sheet->setCellValue("C{$row}", $metric['unit']);

            foreach ($data as $i => $d) {
                $val = $d[$metric['key']] ?? 0;
                $sheet->setCellValue($cols[$i] . $row, $val);
                $fmt = !empty($metric['decimal']) ? '#,##0.00' : $numFormat;
                $sheet->getStyle($cols[$i] . $row)->getNumberFormat()->setFormatCode($fmt);
            }

            $this->applyRowStyle($sheet, $row, $metric['style']);
            $row++;
        }

        return $row;
    }

    private function writePartsRows($sheet, int $row, array $data, array $cols, string $numFormat): int
    {
        $groups = [
            ['sub' => 'Parts via Workshop', 'items' => [
                ['label' => 'Pendapatan Parts via WS', 'key' => 'parts_via_ws_revenue', 'style' => 'revenue'],
            ]],
            ['sub' => 'Parts via Counter', 'items' => [
                ['label' => 'Pendapatan Counter', 'key' => 'counter_revenue', 'style' => 'revenue'],
                ['label' => 'HPP Counter', 'key' => 'counter_hpp', 'style' => 'normal'],
                ['label' => 'Gross Margin Counter', 'key' => 'counter_gross_margin', 'style' => 'total'],
            ]],
            ['sub' => 'Dead Stock', 'items' => [
                ['label' => 'Pendapatan Dead Stock', 'key' => 'dead_stock_revenue', 'style' => 'revenue'],
                ['label' => 'HPP Dead Stock', 'key' => 'dead_stock_hpp', 'style' => 'normal'],
                ['label' => 'Gross Margin Dead Stock', 'key' => 'dead_stock_gross_margin', 'style' => 'total'],
            ]],
            ['sub' => 'Merchandise (MB6)', 'items' => [
                ['label' => 'Pendapatan Merchandise', 'key' => 'merchandise_revenue', 'style' => 'revenue'],
                ['label' => 'HPP Merchandise', 'key' => 'merchandise_hpp', 'style' => 'normal'],
                ['label' => 'Gross Margin Merchandise', 'key' => 'merchandise_gross_margin', 'style' => 'total'],
            ]],
        ];

        foreach ($groups as $group) {
            // Sub-section header
            $sheet->setCellValue("B{$row}", $group['sub']);
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($group['items'] as $item) {
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->setCellValue("C{$row}", 'Rp');
                foreach ($data as $i => $d) {
                    $sheet->setCellValue($cols[$i] . $row, $d[$item['key']] ?? 0);
                    $sheet->getStyle($cols[$i] . $row)->getNumberFormat()->setFormatCode($numFormat);
                }
                $this->applyRowStyle($sheet, $row, $item['style']);
                $row++;
            }
        }

        return $row;
    }

    private function applyRowStyle($sheet, int $row, string $style): void
    {
        $range = "A{$row}:K{$row}";
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        switch ($style) {
            case 'revenue':
                $sheet->getStyle($range)->getFont()->setBold(true);
                $sheet->getStyle($range)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E2F0D9');
                break;
            case 'metric':
                $sheet->getStyle($range)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF2CC');
                break;
            case 'total':
                $sheet->getStyle($range)->getFont()->setBold(true);
                $sheet->getStyle($range)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DAE3F3');
                break;
        }
    }
}
