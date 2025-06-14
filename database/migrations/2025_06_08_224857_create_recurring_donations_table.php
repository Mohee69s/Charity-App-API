<?php

use App\Models\Campaign;
use App\Models\User;
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
        Schema::create('recurring_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->enum('type',['health','food','education','most_need']);
            $table->bigInteger('amount');
            $table->enum('period',['daily','weekly','monthly','yearly']);
            $table->date('start_date');
            $table->date('next_run')->nullable();
            $table->integer('run_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('reminder_notification',['one_day_before','when_the_time_comes'])->default('one_day_before');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_donations');
    }
};
