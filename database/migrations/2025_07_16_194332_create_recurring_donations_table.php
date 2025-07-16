<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_donations', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount');
            $table->date('start_date');
            $table->timestamp('next_run')->nullable();
            $table->integer('run_count')->nullable()->default(0);
            $table->integer('reminder')->nullable()->default(1);
            $table->boolean('is_active')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->integer('user_id')->nullable();

            $table->unique(['id'], 'recurring_donations_pkey');
        });
        DB::statement("alter table \"recurring_donations\" add column \"type\" recurring_donations_type_enum null default 'educational'");
        DB::statement("alter table \"recurring_donations\" add column \"period\" recurring_donations_period_enum not null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_donations');
    }
};
