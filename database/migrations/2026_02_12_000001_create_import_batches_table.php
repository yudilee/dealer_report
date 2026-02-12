<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('file_type'); // labour, parts, throughput, wip, deadstock
            $table->string('franchise'); // pc, cv
            $table->string('filename');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->integer('record_count')->default(0);
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
