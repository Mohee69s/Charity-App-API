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
        Schema::table('submit_users_opportunities', function (Blueprint $table) {
            $table->foreign(['opportunity_id'], 'FK_123010490c01fdd09777e3b95f1')->references(['id'])->on('volunteer_opportunities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'FK_4fd8a6351290fea0fbebaa13787')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submit_users_opportunities', function (Blueprint $table) {
            $table->dropForeign('FK_123010490c01fdd09777e3b95f1');
            $table->dropForeign('FK_4fd8a6351290fea0fbebaa13787');
        });
    }
};
