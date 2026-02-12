<?php

namespace App\Exports;

use App\Services\ReportCalculationService;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MonthlyReportExport implements WithEvents, WithTitle
{
    private int $year;
    private int $startMonth;

    public function __construct(int $year, int $startMonth = 1)
    {
        $this->year = $year;
        $this->startMonth = $startMonth;
    }

    public function title(): string
    {
        return 'MDR - ALL';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $service = new ReportCalculationService();
                $report = $service->calculateMDR($this->year, $this->startMonth);

                $this->buildSheet($sheet, $report);
            },
        ];
    }

    private function buildSheet($sheet, array $report): void
    {
        $periods = $report['periods'];
        $numFormat = '#,##0';

        // Title
        $sheet->setCellValue('A1', 'MONTHLY DEALER REPORT');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', $this->year);

        // Period Headers
        $sheet->setCellValue('A4', '#');
        $sheet->setCellValue('B4', 'Description');
        $sheet->setCellValue('C4', 'Unit');
        $cols = ['D', 'F', 'H', 'J'];
        foreach ($periods as $i => $label) {
            $col = $cols[$i];
            $nextCol = chr(ord($col) + 1);
            $sheet->setCellValue($col . '4', $label);
            $sheet->mergeCells($col . '4:' . $nextCol . '4');
            $sheet->setCellValue($col . '5', 'Amount');
            $sheet->setCellValue($nextCol . '5', '%');
        }

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

        // Unit Sales placeholders
        foreach (['UNIT SALES - PC', 'UNIT SALES - CV'] as $title) {
            $sheet->setCellValue("B{$row}", $title);
            $sheet->mergeCells("A{$row}:K{$row}");
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0CECE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;
            $sheet->setCellValue("B{$row}", 'Coming soon');
            $row++;
        }

        // Same sections as WDR
        $sections = [
            ['title' => 'WORKSHOP - PC', 'data' => $report['workshop_pc'], 'type' => 'workshop'],
            ['title' => 'WORKSHOP - BODY SHOP', 'data' => $report['bodyshop'], 'type' => 'workshop'],
            ['title' => 'WORKSHOP - CV', 'data' => $report['workshop_cv'], 'type' => 'workshop'],
            ['title' => 'PARTS - PC', 'data' => $report['parts_pc'], 'type' => 'parts'],
            ['title' => 'PARTS - CV', 'data' => $report['parts_cv'], 'type' => 'parts'],
        ];

        foreach ($sections as $section) {
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

        $sheet->freezePane('D6');
    }

    private function writeWorkshopRows($sheet, int $row, array $data, array $cols, string $numFormat): int
    {
        $metrics = [
            ['label' => 'Pendapatan Labor', 'key' => 'pendapatan_labor', 'unit' => 'Rp', 'style' => 'revenue'],
            ['label' => 'Total Unit', 'key' => 'total_unit', 'unit' => 'Unit', 'style' => 'metric'],
            ['label' => 'Avg. Throughput/Hari', 'key' => 'avg_throughput_per_day', 'unit' => 'Unit', 'style' => 'metric'],
            ['label' => 'Avg. Jam Jual/Unit', 'key' => 'avg_jam_jual_per_unit', 'unit' => 'Jam', 'style' => 'metric'],
            ['label' => 'Avg. Labor/Jam', 'key' => 'avg_labor_per_jam', 'unit' => 'Rp', 'style' => 'metric'],
            ['label' => 'Avg. Spend/Unit', 'key' => 'avg_spend_per_unit', 'unit' => 'Rp', 'style' => 'metric'],
            ['label' => 'Pendapatan Sublet', 'key' => 'pendapatan_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'HPP Sublet', 'key' => 'hpp_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'Gross Margin Sublet', 'key' => 'gross_margin_sublet', 'unit' => 'Rp', 'style' => 'normal'],
            ['label' => 'Total Gross Margin', 'key' => 'gross_margin', 'unit' => 'Rp', 'style' => 'total'],
        ];

        foreach ($metrics as $metric) {
            $sheet->setCellValue("B{$row}", $metric['label']);
            $sheet->setCellValue("C{$row}", $metric['unit']);
            foreach ($data as $i => $d) {
                $sheet->setCellValue($cols[$i] . $row, $d[$metric['key']] ?? 0);
                $sheet->getStyle($cols[$i] . $row)->getNumberFormat()->setFormatCode($numFormat);
            }
            $this->applyRowStyle($sheet, $row, $metric['style']);
            $row++;
        }
        return $row;
    }

    private function writePartsRows($sheet, int $row, array $data, array $cols, string $numFormat): int
    {
        $items = [
            ['label' => 'Pendapatan Parts via WS', 'key' => 'parts_via_ws_revenue', 'style' => 'revenue'],
            ['label' => 'Pendapatan Counter', 'key' => 'counter_revenue', 'style' => 'revenue'],
            ['label' => 'HPP Counter', 'key' => 'counter_hpp', 'style' => 'normal'],
            ['label' => 'Gross Margin Counter', 'key' => 'counter_gross_margin', 'style' => 'total'],
            ['label' => 'Pendapatan Dead Stock', 'key' => 'dead_stock_revenue', 'style' => 'revenue'],
            ['label' => 'HPP Dead Stock', 'key' => 'dead_stock_hpp', 'style' => 'normal'],
            ['label' => 'Gross Margin Dead Stock', 'key' => 'dead_stock_gross_margin', 'style' => 'total'],
            ['label' => 'Pendapatan Merchandise', 'key' => 'merchandise_revenue', 'style' => 'revenue'],
            ['label' => 'HPP Merchandise', 'key' => 'merchandise_hpp', 'style' => 'normal'],
            ['label' => 'Gross Margin Merchandise', 'key' => 'merchandise_gross_margin', 'style' => 'total'],
        ];

        foreach ($items as $item) {
            $sheet->setCellValue("B{$row}", $item['label']);
            $sheet->setCellValue("C{$row}", 'Rp');
            foreach ($data as $i => $d) {
                $sheet->setCellValue($cols[$i] . $row, $d[$item['key']] ?? 0);
                $sheet->getStyle($cols[$i] . $row)->getNumberFormat()->setFormatCode($numFormat);
            }
            $this->applyRowStyle($sheet, $row, $item['style']);
            $row++;
        }
        return $row;
    }

    private function applyRowStyle($sheet, int $row, string $style): void
    {
        $range = "A{$row}:K{$row}";
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        match ($style) {
            'revenue' => $sheet->getStyle($range)->getFont()->setBold(true) &&
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2F0D9'),
            'metric' => $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC'),
            'total' => $sheet->getStyle($range)->getFont()->setBold(true) &&
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DAE3F3'),
            default => null,
        };
    }
}
