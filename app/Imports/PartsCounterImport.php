<?php

namespace App\Imports;

use App\Models\PartsCounterRecord;
use App\Services\DateParserService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PartsCounterImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    private string $franchise;
    private int $batchId;
    private int $count = 0;

    public function __construct(string $franchise, int $batchId)
    {
        $this->franchise = $franchise;
        $this->batchId = $batchId;
    }

    /**
     * Column mapping (0-indexed):
     * 0: Inv.Date, 1: Invoice, 2: WIPNO, 3: Part No., 4: Description,
     * 5: Quantity, 6: Sale value, 7: Cost value, 8: Profit, 9: G.P.%,
     * 10: Account, 11: Customer name, 12-15: Free text ×4
     */
    public function model(array $row): ?PartsCounterRecord
    {
        $invDate = DateParserService::parse($row[0] ?? null);
        if (!$invDate) {
            return null; // Skip headers, totals, and non-data rows
        }

        // Skip if no sale value and no cost value (likely a sub-header row)
        $saleValue = DateParserService::parseDecimal($row[6] ?? 0);
        $costValue = DateParserService::parseDecimal($row[7] ?? 0);
        if ($saleValue == 0 && $costValue == 0) {
            return null;
        }

        $this->count++;

        return new PartsCounterRecord([
            'inv_date' => $invDate,
            'invoice_no' => $row[1] ?? null,
            'wip_no' => DateParserService::parseInt($row[2] ?? null),
            'part_no' => trim($row[3] ?? ''),
            'description' => $row[4] ?? null,
            'quantity' => DateParserService::parseDecimal($row[5] ?? 0),
            'sale_value' => $saleValue,
            'cost_value' => $costValue,
            'profit' => DateParserService::parseDecimal($row[8] ?? 0),
            'gp_percent' => DateParserService::parseDecimal($row[9] ?? 0),
            'account_no' => trim($row[10] ?? ''),
            'customer_name' => trim($row[11] ?? ''),
            'source_text' => $row[12] ?? null,
            'franchise' => $this->franchise,
            'import_batch_id' => $this->batchId,
        ]);
    }

    public function startRow(): int { return 2; }
    public function getCount(): int { return $this->count; }
    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
}
