<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabourRecord extends Model
{
    protected $fillable = [
        'inv_date', 'inv_no', 'wip_no', 'line_no', 'rts_code', 'description',
        'type', 'allowed_hours', 'rate', 'net', 'taken_hours',
        'mechanic', 'account_no', 'chassis', 'fr', 'franchise', 'import_batch_id',
    ];

    protected $casts = ['inv_date' => 'date'];

    public function importBatch(): BelongsTo { return $this->belongsTo(ImportBatch::class); }
}
