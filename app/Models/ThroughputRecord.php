<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThroughputRecord extends Model
{
    protected $fillable = [
        'inv_no', 'account_no', 'inv_date', 'department', 'wip_no',
        'chassis', 'check_in', 'check_out',
        'labor_amount', 'part_amount', 'sublet_amount', 'sundry_amount',
        'vat_amount', 'total_amount', 'cost_sublet',
        'fr', 'service_advisor', 'code', 'sale_type', 'f_col',
        'customer_name', 'order_no', 'registration',
        'franchise', 'import_batch_id',
    ];

    protected $casts = [
        'inv_date' => 'date',
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function importBatch(): BelongsTo { return $this->belongsTo(ImportBatch::class); }
}
