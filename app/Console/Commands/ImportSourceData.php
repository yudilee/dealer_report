<?php

namespace App\Console\Commands;

use App\Imports\LabourImport;
use App\Imports\PartsCounterImport;
use App\Imports\ThroughputImport;
use App\Imports\UnitImport;
use App\Imports\DeadStockImport;
use App\Models\ImportBatch;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportSourceData extends Command
{
    protected $signature = 'import:source-data
                            {type : File type: labour, parts, throughput, wip, deadstock}
                            {franchise : Franchise: pc or cv}
                            {file : Path to the source XLS file}';

    protected $description = 'Import source data from XLS files into the database';

    private array $importerMap = [
        'labour' => LabourImport::class,
        'parts' => PartsCounterImport::class,
        'throughput' => ThroughputImport::class,
        'wip' => UnitImport::class,
        'deadstock' => DeadStockImport::class,
    ];

    public function handle(): int
    {
        $type = strtolower($this->argument('type'));
        $franchise = strtolower($this->argument('franchise'));
        $file = $this->argument('file');

        if (!array_key_exists($type, $this->importerMap)) {
            $this->error("Invalid type: {$type}. Must be one of: " . implode(', ', array_keys($this->importerMap)));
            return 1;
        }

        if (!in_array($franchise, ['pc', 'cv'])) {
            $this->error("Invalid franchise: {$franchise}. Must be 'pc' or 'cv'.");
            return 1;
        }

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Importing {$type} ({$franchise}) from: {$file}");

        // Delete previous imports of same type + franchise
        $oldBatches = ImportBatch::where('file_type', $type)
            ->where('franchise', $franchise)
            ->get();
        foreach ($oldBatches as $batch) {
            $batch->delete(); // cascade deletes records
        }

        // Create import batch
        $batch = ImportBatch::create([
            'file_type' => $type,
            'franchise' => $franchise,
            'filename' => basename($file),
            'imported_at' => now(),
        ]);

        // Run import
        $importerClass = $this->importerMap[$type];
        $importer = new $importerClass($franchise, $batch->id);

        Excel::import($importer, $file);

        // Update batch record count
        $batch->update(['record_count' => $importer->getCount()]);

        $this->info("✓ Imported {$importer->getCount()} records into batch #{$batch->id}");

        return 0;
    }
}
