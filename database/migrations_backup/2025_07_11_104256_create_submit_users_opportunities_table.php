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
        Schema::create('submit_users_opportunities', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('opportunity_id');
            $table->boolean('approved')->nullable()->default(false);
            $table->timestamp('submitted_at')->nullable()->default(DB::raw("now()"));

            $table->primary(['user_id', 'opportunity_id']);
            $table->unique(['opportunity_id', 'user_id'], 'submit_users_opportunities_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submit_users_opportunities');
    }
};
