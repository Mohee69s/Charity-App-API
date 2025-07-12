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
        Schema::create('donations', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount');
            $table->timestamp('donation_date')->nullable()->default(DB::raw("now()"));
            $table->boolean('recurring')->nullable();
            $table->integer('campaign_id')->nullable();
            $table->integer('user_id')->nullable();

            $table->unique(['id'], 'donations_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
