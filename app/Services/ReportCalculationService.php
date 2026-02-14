<?php

namespace App\Services;

use App\Models\DeadStockRecord;
use App\Models\LabourRecord;
use App\Models\PartsCounterRecord;
use App\Models\ThroughputRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportCalculationService
{
    /**
     * Calculate WDR (Weekly Dealer Report) for a given year and month.
     * Returns 4 weeks of data for each section.
     */
    public function calculateWDR(int $year, int $month): array
    {
        $periods = $this->getWeeklyPeriods($year, $month);
        return $this->calculateReport($periods);
    }

    /**
     * Calculate MDR (Monthly Dealer Report) for a given year.
     * Returns 4 months of data for each section.
     */
    public function calculateMDR(int $year, int $startMonth = 1): array
    {
        $periods = $this->getMonthlyPeriods($year, $startMonth);
        return $this->calculateReport($periods);
    }

    /**
     * Generate 4 weekly periods for a given month.
     */
    private function getWeeklyPeriods(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $periods = [];

        for ($i = 0; $i < 4; $i++) {
            $weekStart = $start->copy()->addDays($i * 7);
            $weekEnd = ($i === 3) ? $end : $weekStart->copy()->addDays(6);
            if ($weekStart->gt($end)) break;

            $periods[] = [
                'label' => 'WEEK - ' . ($i + 1) . ' (' . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M') . ')',
                'start' => $weekStart,
                'end' => $weekEnd,
            ];
        }

        return $periods;
    }

    /**
     * Generate 4 monthly periods.
     */
    private function getMonthlyPeriods(int $year, int $startMonth): array
    {
        $periods = [];
        for ($i = 0; $i < 4; $i++) {
            $m = $startMonth + $i;
            $y = $year;
            if ($m > 12) { $m -= 12; $y++; }
            $start = Carbon::create($y, $m, 1);
            $end = $start->copy()->endOfMonth();

            $periods[] = [
                'label' => strtoupper($start->format('F Y')),
                'start' => $start,
                'end' => $end,
            ];
        }
        return $periods;
    }

    /**
     * Core calculation engine — computes all report sections for given periods.
     */
    private function calculateReport(array $periods): array
    {
        // Get dead stock part numbers
        $deadStockPartsPC = DeadStockRecord::where('franchise', 'pc')
            ->pluck('part_no')->unique()->map(fn($p) => trim($p))->toArray();
        $deadStockPartsCV = DeadStockRecord::where('franchise', 'cv')
            ->pluck('part_no')->unique()->map(fn($p) => trim($p))->toArray();

        $report = [
            'periods' => collect($periods)->pluck('label')->toArray(),
            'workshop_pc' => [],
            'bodyshop' => [],
            'workshop_cv' => [],
            'parts_pc' => [],
            'parts_cv' => [],
        ];

        foreach ($periods as $i => $period) {
            $start = $period['start'];
            $end = $period['end'];

            // === WORKSHOP PC (Department W) ===
            $report['workshop_pc'][$i] = $this->calcWorkshop('pc', 'W', $start, $end);

            // === BODY SHOP (PC, Department B) ===
            $report['bodyshop'][$i] = $this->calcWorkshop('pc', 'B', $start, $end);

            // === WORKSHOP CV ===
            $report['workshop_cv'][$i] = $this->calcWorkshop('cv', 'W', $start, $end);

            // === PARTS PC ===
            $report['parts_pc'][$i] = $this->calcParts('pc', $start, $end, $deadStockPartsPC);

            // === PARTS CV ===
            $report['parts_cv'][$i] = $this->calcParts('cv', $start, $end, $deadStockPartsCV);
        }

        return $report;
    }

    /**
     * Calculate workshop metrics for a franchise + department + period.
     */
    private function calcWorkshop(string $franchise, string $dept, Carbon $start, Carbon $end): array
    {
        // Throughput data
        $throughput = ThroughputRecord::where('franchise', $franchise)
            ->where('department', $dept)
            ->whereBetween('inv_date', [$start, $end])
            ->get();

        $laborRevenue = $throughput->sum('labor_amount');
        $partRevenue = $throughput->sum('part_amount');
        $subletRevenue = $throughput->sum('sublet_amount');
        $sundryRevenue = $throughput->sum('sundry_amount');
        $costSublet = $throughput->sum('cost_sublet');
        $totalAmount = $throughput->sum('total_amount');

        // Unique vehicles (by registration)
        $uniqueRegs = $throughput->pluck('registration')
            ->filter(fn($r) => !empty(trim($r ?? '')))
            ->unique()
            ->count();
        $workingDays = 6; // per week

        // Labour data — fetch ALL labour for invoiced WIPs regardless of when
        // the work was performed, since labour is attributed to the invoice date.
        $throughputWips = $throughput->pluck('wip_no')->unique()->filter()->toArray();
        $deptLabour = LabourRecord::where('franchise', $franchise)
            ->whereIn('wip_no', $throughputWips)
            ->get();

        $totalAllowed = $deptLabour->sum('allowed_hours');
        $totalNet = $deptLabour->sum('net');

        // HPP calculations
        $hppSublet = $costSublet;
        $hppSundry = $sundryRevenue; // Sundry is both revenue and cost
        $hppLabour = $totalNet; // Labour cost = Net amount
        $hppPartsWS = $throughput->sum(function ($row) {
            // Parts cost via WS — we approximate from throughput part column
            // In a real scenario this would be separate, we use the part amount as cost proxy
            return 0; // Will need parts cost data
        });
        $workshopHPP = $hppSublet + $hppSundry + $hppLabour;

        return [
            'pendapatan_labor' => $laborRevenue,
            'total_unit' => $uniqueRegs,
            'avg_throughput_per_day' => $workingDays > 0 ? round($uniqueRegs / $workingDays, 2) : 0,
            'avg_jam_jual_per_unit' => $uniqueRegs > 0 ? round($totalAllowed / $uniqueRegs, 2) : 0,
            'avg_labor_per_jam' => $totalAllowed > 0 ? round($laborRevenue / $totalAllowed, 0) : 0,
            'avg_spend_per_unit' => $uniqueRegs > 0 ? round($laborRevenue / $uniqueRegs, 0) : 0,
            'pendapatan_sublet' => $subletRevenue,
            'hpp_sublet' => $hppSublet,
            'gross_margin_sublet' => $subletRevenue - $hppSublet,
            'pendapatan_sundry' => $sundryRevenue,
            'hpp_sundry' => $hppSundry,
            'workshop_hpp' => $workshopHPP,
            'gross_margin' => $laborRevenue + $subletRevenue + $sundryRevenue - $workshopHPP,
            'part_via_ws' => $partRevenue,
        ];
    }

    /**
     * Calculate parts metrics for a franchise + period.
     * Parts are categorized by part_no:
     *   - Starts with MB6 → Merchandise
     *   - In dead stock list → Dead Stock
     *   - Everything else → Counter
     */
    private function calcParts(string $franchise, Carbon $start, Carbon $end, array $deadStockParts): array
    {
        $parts = PartsCounterRecord::where('franchise', $franchise)
            ->whereBetween('inv_date', [$start, $end])
            ->get();

        $counter = $parts->filter(function ($p) use ($deadStockParts) {
            $pn = trim($p->part_no ?? '');
            return !str_starts_with($pn, 'MB6') && !in_array($pn, $deadStockParts);
        });

        $merchandise = $parts->filter(fn($p) => str_starts_with(trim($p->part_no ?? ''), 'MB6'));

        $deadStock = $parts->filter(function ($p) use ($deadStockParts) {
            $pn = trim($p->part_no ?? '');
            return !str_starts_with($pn, 'MB6') && in_array($pn, $deadStockParts);
        });

        // Parts via workshop (from throughput)
        $throughputParts = ThroughputRecord::where('franchise', $franchise)
            ->whereBetween('inv_date', [$start, $end])
            ->sum('part_amount');

        return [
            'parts_via_ws_revenue' => $throughputParts,
            'counter_revenue' => $counter->sum('sale_value'),
            'counter_hpp' => $counter->sum('cost_value'),
            'counter_gross_margin' => $counter->sum('sale_value') - $counter->sum('cost_value'),
            'dead_stock_revenue' => $deadStock->sum('sale_value'),
            'dead_stock_hpp' => $deadStock->sum('cost_value'),
            'dead_stock_gross_margin' => $deadStock->sum('sale_value') - $deadStock->sum('cost_value'),
            'merchandise_revenue' => $merchandise->sum('sale_value'),
            'merchandise_hpp' => $merchandise->sum('cost_value'),
            'merchandise_gross_margin' => $merchandise->sum('sale_value') - $merchandise->sum('cost_value'),
        ];
    }
}
