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
        Schema::table('volunteer_applications', function (Blueprint $table) {
            $table->foreign(['user_id'], 'FK_6d113cfbff184552bf43b4967b3')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_applications', function (Blueprint $table) {
            $table->dropForeign('FK_6d113cfbff184552bf43b4967b3');
        });
    }
};
