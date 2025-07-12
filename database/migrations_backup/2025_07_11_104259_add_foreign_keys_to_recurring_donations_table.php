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
        Schema::table('recurring_donations', function (Blueprint $table) {
            $table->foreign(['user_id'], 'FK_6278bc1163b305b0de9d204ff86')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_donations', function (Blueprint $table) {
            $table->dropForeign('FK_6278bc1163b305b0de9d204ff86');
        });
    }
};
