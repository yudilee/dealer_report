<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('throughput_records', function (Blueprint $table) {
            $table->id();
            $table->string('inv_no')->nullable();
            $table->string('account_no')->nullable();
            $table->date('inv_date');
            $table->char('department', 1)->nullable(); // W, B
            $table->integer('wip_no');
            $table->string('chassis')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->decimal('labor_amount', 15, 2)->default(0);
            $table->decimal('part_amount', 15, 2)->default(0);
            $table->decimal('sublet_amount', 15, 2)->default(0);
            $table->decimal('sundry_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('cost_sublet', 15, 2)->default(0);
            $table->char('fr', 2)->nullable();
            $table->string('service_advisor')->nullable();
            $table->string('code')->nullable();
            $table->string('sale_type', 5)->nullable(); // R, K, W, I, J, C
            $table->char('f_col', 2)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('order_no')->nullable();
            $table->string('registration')->nullable();
            $table->string('franchise'); // pc, cv
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['inv_date', 'franchise', 'department']);
            $table->index('registration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('throughput_records');
    }
};
