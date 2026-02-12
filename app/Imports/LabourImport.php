<?php

namespace App\Imports;

use App\Models\LabourRecord;
use App\Services\DateParserService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LabourImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private string $franchise;
    private int $batchId;
    private int $count = 0;

    public function __construct(string $franchise, int $batchId)
    {
        $this->franchise = $franchise;
        $this->batchId = $batchId;
    }

    public function model(array $row): ?LabourRecord
    {
        // Headers: Inv_date, Inv_no, WIPNo, Ln, RTsCode, Descr, Type, Allowed, Rate, Net, Taken, Mekanik, Acc No, Chasis, Fr
        $invDate = DateParserService::parse($row['inv_date'] ?? null);
        if (!$invDate) {
            return null;
        }

        $wipNo = DateParserService::parseInt($row['wipno'] ?? null);
        if (!$wipNo) {
            return null;
        }

        $this->count++;

        return new LabourRecord([
            'inv_date' => $invDate,
            'inv_no' => $row['inv_no'] ?? null,
            'wip_no' => $wipNo,
            'line_no' => DateParserService::parseInt($row['ln'] ?? null),
            'rts_code' => $row['rtscode'] ?? null,
            'description' => $row['descr'] ?? null,
            'type' => $row['type'] ?? null,
            'allowed_hours' => DateParserService::parseDecimal($row['allowed'] ?? 0),
            'rate' => DateParserService::parseDecimal($row['rate'] ?? 0),
            'net' => DateParserService::parseDecimal($row['net'] ?? 0),
            'taken_hours' => DateParserService::parseDecimal($row['taken'] ?? 0),
            'mechanic' => $row['mekanik'] ?? null,
            'account_no' => $row['acc_no'] ?? ($row['acc__no'] ?? null),
            'chassis' => $row['chasis'] ?? null,
            'fr' => trim($row['fr'] ?? ''),
            'franchise' => $this->franchise,
            'import_batch_id' => $this->batchId,
        ]);
    }

    public function getCount(): int { return $this->count; }
    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
}
