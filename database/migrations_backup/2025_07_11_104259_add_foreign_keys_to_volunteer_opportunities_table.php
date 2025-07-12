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
        Schema::table('volunteer_opportunities', function (Blueprint $table) {
            $table->foreign(['campaign_id'], 'FK_005021e27f3b930b772db11951c')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_opportunities', function (Blueprint $table) {
            $table->dropForeign('FK_005021e27f3b930b772db11951c');
        });
    }
};
