<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeadStockRecord extends Model
{
    protected $fillable = [
        'sale_date', 'wip_no', 'part_no',
        'opening_stock', 'purchases', 'sales_qty', 'closing_stock',
        'bin_loc', 'code', 'audit_number', 'returns_cat',
        'customer_name', 'account_no', 'inv_no', 'cost_price',
        'description', 'date_last_purchased',
        'franchise', 'import_batch_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'date_last_purchased' => 'date',
    ];

    public function importBatch(): BelongsTo { return $this->belongsTo(ImportBatch::class); }
}
