<?php

namespace App\Imports;

use App\Models\ThroughputRecord;
use App\Services\DateParserService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ThroughputImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
     * 0: Inv NO, 1: Account, 2: Inv.date, 3: D, 4: Wip, 5: Chassis,
     * 6: Check-in, 7: Check-out, 8: Labor, 9: Part, 10: Sublet, 11: Sundry,
     * 12: Vat, 13: Fr, 14: Total, 15: Cost Sublet, 16: Service Advisor,
     * 17: Code, 18: Sal T, 19: F, 20: Customer name, 21: ORDER NO, 22: Registration
     */
    public function model(array $row): ?ThroughputRecord
    {
        $invDate = DateParserService::parse($row[2] ?? null);
        if (!$invDate) {
            return null;
        }

        $wipNo = DateParserService::parseInt($row[4] ?? null);
        if (!$wipNo) {
            return null;
        }

        $this->count++;

        return new ThroughputRecord([
            'inv_no' => $row[0] ?? null,
            'account_no' => $row[1] ?? null,
            'inv_date' => $invDate,
            'department' => trim($row[3] ?? ''),
            'wip_no' => $wipNo,
            'chassis' => $row[5] ?? null,
            'check_in' => DateParserService::parse($row[6] ?? null),
            'check_out' => DateParserService::parse($row[7] ?? null),
            'labor_amount' => DateParserService::parseDecimal($row[8] ?? 0),
            'part_amount' => DateParserService::parseDecimal($row[9] ?? 0),
            'sublet_amount' => DateParserService::parseDecimal($row[10] ?? 0),
            'sundry_amount' => DateParserService::parseDecimal($row[11] ?? 0),
            'vat_amount' => DateParserService::parseDecimal($row[12] ?? 0),
            'total_amount' => DateParserService::parseDecimal($row[14] ?? 0),
            'cost_sublet' => DateParserService::parseDecimal($row[15] ?? 0),
            'fr' => trim($row[13] ?? ''),
            'service_advisor' => $row[16] ?? null,
            'code' => $row[17] ?? null,
            'sale_type' => trim($row[18] ?? ''),
            'f_col' => trim($row[19] ?? ''),
            'customer_name' => $row[20] ?? null,
            'order_no' => $row[21] ?? null,
            'registration' => trim($row[22] ?? ''),
            'franchise' => $this->franchise,
            'import_batch_id' => $this->batchId,
        ]);
    }

    public function startRow(): int { return 2; } // skip header
    public function getCount(): int { return $this->count; }
    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
}
