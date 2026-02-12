<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts_counter_records', function (Blueprint $table) {
            $table->id();
            $table->date('inv_date');
            $table->string('invoice_no')->nullable();
            $table->integer('wip_no')->nullable();
            $table->string('part_no')->nullable();
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('sale_value', 15, 2)->default(0);
            $table->decimal('cost_value', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('gp_percent', 8, 2)->default(0);
            $table->string('account_no')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('source_text')->nullable();
            $table->string('franchise'); // pc, cv
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['inv_date', 'franchise']);
            $table->index('part_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_counter_records');
    }
};
