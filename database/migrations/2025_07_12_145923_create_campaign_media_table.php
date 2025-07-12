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
        Schema::create('campaign_media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('description')->nullable();
            $table->timestamp('uploaded_at')->nullable()->default(DB::raw("now()"));
            $table->integer('campaign_id')->nullable();

            $table->unique(['id'], 'campaign_media_pkey');
        });
        DB::statement("alter table \"campaign_media\" add column \"media_type\" campaign_media_type_enum not null default 'image'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_media');
    }
};
