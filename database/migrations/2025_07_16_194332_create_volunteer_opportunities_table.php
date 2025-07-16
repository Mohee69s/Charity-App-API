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
        Schema::create('volunteer_opportunities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('tasks')->nullable();
            $table->string('duration')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->integer('campaign_id')->nullable();

            $table->unique(['id'], 'volunteer_opportunities_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_opportunities');
    }
};
