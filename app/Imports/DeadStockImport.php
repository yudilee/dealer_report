<?php

namespace App\Imports;

use App\Models\DeadStockRecord;
use App\Services\DateParserService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DeadStockImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
     * PC (16 cols): Date, WIP number, Part number, Opening Stock, Purchases, Sales,
     *   Closing Stock, Bin/loc, code, audit number, Returns cat, Customer name,
     *   Account, Inv number, Cost price, Date last purchased
     *
     * CV (17 cols, extra Description before Date last purchased):
     *   Date, WIP No, Part number, Opening St, Purchases, Sales,
     *   Closing Stock, Bin/loc, code, audit No, Ret cat, Customer name,
     *   Account, Inv number, Cost price, Description, Date last purchased
     */
    public function model(array $row): ?DeadStockRecord
    {
        $partNo = trim($row[2] ?? '');
        if (empty($partNo)) {
            return null;
        }

        $saleDate = DateParserService::parse($row[0] ?? null);
        if (!$saleDate) {
            return null;
        }

        $isCV = $this->franchise === 'cv';
        $descIdx = $isCV ? 15 : null;
        $lastPurchIdx = $isCV ? 16 : 15;

        $this->count++;

        return new DeadStockRecord([
            'sale_date' => $saleDate,
            'wip_no' => DateParserService::parseInt($row[1] ?? null),
            'part_no' => $partNo,
            'opening_stock' => DateParserService::parseDecimal($row[3] ?? 0),
            'purchases' => DateParserService::parseDecimal($row[4] ?? 0),
            'sales_qty' => DateParserService::parseDecimal($row[5] ?? 0),
            'closing_stock' => DateParserService::parseDecimal($row[6] ?? 0),
            'bin_loc' => $row[7] ?? null,
            'code' => $row[8] ?? null,
            'audit_number' => DateParserService::parseInt($row[9] ?? null),
            'returns_cat' => DateParserService::parseInt($row[10] ?? null),
            'customer_name' => $row[11] ?? null,
            'account_no' => $row[12] ?? null,
            'inv_no' => $row[13] ?? null,
            'cost_price' => DateParserService::parseDecimal($row[14] ?? 0),
            'description' => $isCV ? ($row[$descIdx] ?? null) : null,
            'date_last_purchased' => DateParserService::parse($row[$lastPurchIdx] ?? null),
            'franchise' => $this->franchise,
            'import_batch_id' => $this->batchId,
        ]);
    }

    public function startRow(): int { return 2; }
    public function getCount(): int { return $this->count; }
    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
}
