<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_records', function (Blueprint $table) {
            $table->id();
            $table->date('inv_date');
            $table->string('inv_no')->nullable();
            $table->integer('wip_no');
            $table->integer('line_no')->nullable();
            $table->string('rts_code')->nullable();
            $table->string('description')->nullable();
            $table->char('type', 1)->nullable(); // T, A, I, G, Y
            $table->decimal('allowed_hours', 10, 2)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('net', 15, 2)->default(0);
            $table->decimal('taken_hours', 10, 2)->default(0);
            $table->string('mechanic')->nullable();
            $table->string('account_no')->nullable();
            $table->string('chassis')->nullable();
            $table->char('fr', 2)->nullable(); // M, Z, V, T
            $table->string('franchise'); // pc, cv
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['inv_date', 'franchise']);
            $table->index('wip_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_records');
    }
};
