<?php

namespace App\Imports;

use App\Models\UnitRecord;
use App\Services\DateParserService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UnitImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
     * PC (19 cols): Fr, JL, WIP, Date in, Check out, Reg. No., Chassis, Model variant,
     *   Customer name, Alamat, Kota, Phone, Account no, Owning op, Creating op,
     *   acc, Department, Date registered, Inv nO
     *
     * CV (18 cols, no JL): Fr, WIP, Date in, Check out, Reg. No., Chassis, Model variant,
     *   Customer name, Alamat, Kota, Phone, Account no, Owning op, Create op,
     *   acc, Department, Date registered, Inv nO
     */
    public function model(array $row): ?UnitRecord
    {
        $isPC = $this->franchise === 'pc';
        $offset = $isPC ? 0 : -1; // CV has no JL column, so all subsequent cols shift left by 1

        $fr = trim($row[0] ?? '');
        if (empty($fr) || strlen($fr) > 3) {
            return null; // Skip admin rows like "name: BUDI"
        }

        $wipNo = DateParserService::parseInt($row[$isPC ? 2 : 1] ?? null);
        if (!$wipNo) {
            return null;
        }

        $this->count++;

        return new UnitRecord([
            'fr' => $fr,
            'jl' => $isPC ? DateParserService::parseInt($row[1] ?? null) : null,
            'wip_no' => $wipNo,
            'date_in' => DateParserService::parse($row[$isPC ? 3 : 2] ?? null),
            'check_out' => DateParserService::parse($row[$isPC ? 4 : 3] ?? null),
            'reg_no' => trim($row[$isPC ? 5 : 4] ?? ''),
            'chassis' => $row[$isPC ? 6 : 5] ?? null,
            'model_variant' => $row[$isPC ? 7 : 6] ?? null,
            'customer_name' => $row[$isPC ? 8 : 7] ?? null,
            'address' => $row[$isPC ? 9 : 8] ?? null,
            'city' => $row[$isPC ? 10 : 9] ?? null,
            'phone' => $row[$isPC ? 11 : 10] ?? null,
            'account_no' => trim($row[$isPC ? 12 : 11] ?? ''),
            'owning_op' => DateParserService::parseInt($row[$isPC ? 13 : 12] ?? null),
            'creating_op' => DateParserService::parseInt($row[$isPC ? 14 : 13] ?? null),
            'acc_type' => trim($row[$isPC ? 15 : 14] ?? ''),
            'department' => trim($row[$isPC ? 16 : 15] ?? ''),
            'date_registered' => $row[$isPC ? 17 : 16] ?? null,
            'inv_no' => $row[$isPC ? 18 : 17] ?? null,
            'franchise' => $this->franchise,
            'import_batch_id' => $this->batchId,
        ]);
    }

    public function startRow(): int { return 2; }
    public function getCount(): int { return $this->count; }
    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
}
