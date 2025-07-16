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
        Schema::table('in_kind', function (Blueprint $table) {
            $table->foreign(['campaign_id'], 'FK_65879f53bd2cde205fd7c99c423')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_kind', function (Blueprint $table) {
            $table->dropForeign('FK_65879f53bd2cde205fd7c99c423');
        });
    }
};
