<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaign_media', function (Blueprint $table) {
            $table->foreign(['campaign_id'], 'FK_0d2df50132408c1ed365f2fb63a')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_media', function (Blueprint $table) {
            $table->dropForeign('FK_0d2df50132408c1ed365f2fb63a');
        });
    }
};
