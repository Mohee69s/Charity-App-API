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
        Schema::create('campaign_volunteers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));
            $table->integer('userId');
            $table->integer('campaignId');

            $table->unique(['id'], 'campaign_volunteers_pkey');
        });
        DB::statement("alter table \"campaign_volunteers\" add column \"status\" campaign_volunteer_status_enum not null default 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_volunteers');
    }
};
