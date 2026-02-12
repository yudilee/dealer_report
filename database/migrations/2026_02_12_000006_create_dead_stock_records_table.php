<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dead_stock_records', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date')->nullable();
            $table->integer('wip_no')->nullable();
            $table->string('part_no');
            $table->decimal('opening_stock', 10, 2)->default(0);
            $table->decimal('purchases', 10, 2)->default(0);
            $table->decimal('sales_qty', 10, 2)->default(0);
            $table->decimal('closing_stock', 10, 2)->default(0);
            $table->string('bin_loc')->nullable();
            $table->string('code')->nullable();
            $table->integer('audit_number')->nullable();
            $table->integer('returns_cat')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('inv_no')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->string('description')->nullable(); // CV only
            $table->date('date_last_purchased')->nullable();
            $table->string('franchise'); // pc, cv
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['franchise', 'part_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dead_stock_records');
    }
};
