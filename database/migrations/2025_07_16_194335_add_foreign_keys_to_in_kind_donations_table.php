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
        Schema::table('in_kind_donations', function (Blueprint $table) {
            $table->foreign(['user_id'], 'FK_792662bbd4f5726ff59abd8d7be')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['campaign_id'], 'FK_7ac8410ee32545bf360d5a0adeb')->references(['id'])->on('campaigns')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['in_kind_id'], 'FK_7de884c9f4a9d0d8b5d84fa91a2')->references(['id'])->on('in_kind')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_kind_donations', function (Blueprint $table) {
            $table->dropForeign('FK_792662bbd4f5726ff59abd8d7be');
            $table->dropForeign('FK_7ac8410ee32545bf360d5a0adeb');
            $table->dropForeign('FK_7de884c9f4a9d0d8b5d84fa91a2');
        });
    }
};
