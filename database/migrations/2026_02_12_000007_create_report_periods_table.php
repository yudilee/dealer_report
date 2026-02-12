<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_periods', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // weekly, monthly
            $table->string('period_label'); // e.g. "WEEK - 1 (2 Feb - 7 Feb)"
            $table->integer('period_number'); // 1-4
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_periods');
    }
};
