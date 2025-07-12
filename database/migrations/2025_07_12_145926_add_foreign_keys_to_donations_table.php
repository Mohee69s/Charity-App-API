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
        Schema::table('donations', function (Blueprint $table) {
            $table->foreign(['campaign_id'], 'FK_6ad4405f42816956aa8a89bc9fb')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'FK_e0a522570e35074125c86d817ea')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign('FK_6ad4405f42816956aa8a89bc9fb');
            $table->dropForeign('FK_e0a522570e35074125c86d817ea');
        });
    }
};
