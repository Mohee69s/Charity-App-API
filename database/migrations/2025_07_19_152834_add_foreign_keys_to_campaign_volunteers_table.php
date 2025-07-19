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
        Schema::table('campaign_volunteers', function (Blueprint $table) {
            $table->foreign(['userId'], 'FK_7d26e1ff10cd108ba1f56fc61b0')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['campaignId'], 'FK_983415a5723b5643f87eaf1c3ac')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_volunteers', function (Blueprint $table) {
            $table->dropForeign('FK_7d26e1ff10cd108ba1f56fc61b0');
            $table->dropForeign('FK_983415a5723b5643f87eaf1c3ac');
        });
    }
};
