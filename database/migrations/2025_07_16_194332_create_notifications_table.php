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
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message');
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->integer('user_id')->nullable();

            $table->unique(['id'], 'notifications_pkey');
        });
        DB::statement("alter table \"notifications\" add column \"status\" notifications_status_enum null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
