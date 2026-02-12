<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'file_type', 'franchise', 'filename',
        'period_start', 'period_end', 'record_count', 'imported_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'imported_at' => 'datetime',
    ];

    public function labourRecords(): HasMany { return $this->hasMany(LabourRecord::class); }
    public function throughputRecords(): HasMany { return $this->hasMany(ThroughputRecord::class); }
    public function partsCounterRecords(): HasMany { return $this->hasMany(PartsCounterRecord::class); }
    public function unitRecords(): HasMany { return $this->hasMany(UnitRecord::class); }
    public function deadStockRecords(): HasMany { return $this->hasMany(DeadStockRecord::class); }
}
