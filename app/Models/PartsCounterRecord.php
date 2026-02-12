<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartsCounterRecord extends Model
{
    protected $fillable = [
        'inv_date', 'invoice_no', 'wip_no', 'part_no', 'description',
        'quantity', 'sale_value', 'cost_value', 'profit', 'gp_percent',
        'account_no', 'customer_name', 'source_text',
        'franchise', 'import_batch_id',
    ];

    protected $casts = ['inv_date' => 'date'];

    public function importBatch(): BelongsTo { return $this->belongsTo(ImportBatch::class); }
}
