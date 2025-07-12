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
        Schema::create('volunteer_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name');
            $table->string('phone_number')->nullable();
            $table->text('skills')->nullable();
            $table->string('available_time')->nullable();
            $table->integer('hours_per_week')->nullable();
            $table->text('previous_experience')->nullable();
            $table->integer('age')->nullable();
            $table->timestamp('submitted_at')->nullable()->default(DB::raw("now()"));
            $table->integer('user_id')->nullable();

            $table->unique(['id'], 'volunteer_applications_pkey');
        });
        DB::statement("alter table \"volunteer_applications\" add column \"gender\" volunteer_applications_gender_enum null default 'male'");
        DB::statement("alter table \"volunteer_applications\" add column \"status\" volunteer_applications_status_enum null default 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_applications');
    }
};
