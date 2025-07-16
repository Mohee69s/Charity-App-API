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
        Schema::create('in_kind_donations', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description');
            $table->boolean('approved')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));
            $table->integer('campaign_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('in_kind_id')->nullable();

            $table->unique(['id'], 'in_kind_donations_pkey');
        });
        DB::statement("alter table \"in_kind_donations\" add column \"status\" in_kind_donations_status_enum null default 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_kind_donations');
    }
};
