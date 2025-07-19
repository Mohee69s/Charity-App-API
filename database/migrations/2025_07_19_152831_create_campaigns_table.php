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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('goal');
            $table->decimal('cost')->nullable()->default(0);
            $table->string('location')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->text('description')->nullable()->default('Nothing');
            $table->boolean('need_volunteers')->nullable()->default(false);
            $table->boolean('need_donations')->nullable()->default(false);
            $table->boolean('need_in_kind_donations')->nullable()->default(false);
            $table->integer('accepted_volunteers_count')->nullable()->default(0);
            $table->integer('required_volunteers_count')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));

            $table->unique(['id'], 'campaigns_pkey');
        });
        DB::statement("alter table \"campaigns\" add column \"status\" campaigns_status_enum null default 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
