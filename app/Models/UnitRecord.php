<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitRecord extends Model
{
    protected $fillable = [
        'fr', 'jl', 'wip_no', 'date_in', 'check_out',
        'reg_no', 'chassis', 'model_variant', 'customer_name',
        'address', 'city', 'phone', 'account_no',
        'owning_op', 'creating_op', 'acc_type', 'department',
        'date_registered', 'inv_no', 'franchise', 'import_batch_id',
    ];

    protected $casts = [
        'date_in' => 'date',
        'check_out' => 'date',
    ];

    public function importBatch(): BelongsTo { return $this->belongsTo(ImportBatch::class); }
}
