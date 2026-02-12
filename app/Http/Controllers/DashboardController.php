<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Services\ReportCalculationService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('report.weekly', ['year' => 2026, 'month' => 1]);
    }

    public function weeklyReport(Request $request)
    {
        $year = (int) $request->get('year', 2026);
        $month = (int) $request->get('month', 1);

        $service = new ReportCalculationService();
        $report = $service->calculateWDR($year, $month);

        $batches = ImportBatch::orderBy('file_type')->orderBy('franchise')->get();

        return view('reports.weekly', compact('report', 'year', 'month', 'batches'));
    }

    public function monthlyReport(Request $request)
    {
        $year = (int) $request->get('year', 2026);
        $startMonth = (int) $request->get('start_month', 1);

        $service = new ReportCalculationService();
        $report = $service->calculateMDR($year, $startMonth);

        $batches = ImportBatch::orderBy('file_type')->orderBy('franchise')->get();

        return view('reports.monthly', compact('report', 'year', 'startMonth', 'batches'));
    }

    public function importPage()
    {
        $batches = ImportBatch::orderBy('file_type')->orderBy('franchise')->get();
        return view('import', compact('batches'));
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'file_type' => 'required|in:labour,parts,throughput,wip,deadstock',
            'franchise' => 'required|in:pc,cv',
        ]);

        $file = $request->file('file');
        $type = $request->input('file_type');
        $franchise = $request->input('franchise');

        // Store temp and run import
        $path = $file->store('imports', 'local');
        $fullPath = storage_path('app/private/' . $path);

        // Call the import command
        \Artisan::call('import:source-data', [
            'type' => $type,
            'franchise' => $franchise,
            'file' => $fullPath,
        ]);

        $output = \Artisan::output();

        return redirect()->route('import.page')->with('success', $output);
    }

    public function exportReport(Request $request, string $type)
    {
        $year = (int) $request->get('year', 2026);

        if ($type === 'weekly') {
            $month = (int) $request->get('month', 1);
            $export = new \App\Exports\WeeklyReportExport($year, $month);
            return Excel::download($export, "WDR_{$year}_{$month}.xlsx");
        } else {
            $startMonth = (int) $request->get('start_month', 1);
            $export = new \App\Exports\MonthlyReportExport($year, $startMonth);
            return Excel::download($export, "MDR_{$year}.xlsx");
        }
    }
}
