<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_records', function (Blueprint $table) {
            $table->id();
            $table->char('fr', 2)->nullable();
            $table->integer('jl')->nullable(); // PC only, null for CV
            $table->integer('wip_no');
            $table->date('date_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('chassis')->nullable();
            $table->string('model_variant')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('account_no')->nullable();
            $table->integer('owning_op')->nullable();
            $table->integer('creating_op')->nullable();
            $table->char('acc_type', 2)->nullable();
            $table->char('department', 1)->nullable(); // W, B
            $table->string('date_registered')->nullable();
            $table->string('inv_no')->nullable();
            $table->string('franchise'); // pc, cv
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['franchise', 'department']);
            $table->index('reg_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_records');
    }
};
