<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPeriod extends Model
{
    protected $fillable = [
        'report_type', 'period_label', 'period_number',
        'start_date', 'end_date', 'year', 'month',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
