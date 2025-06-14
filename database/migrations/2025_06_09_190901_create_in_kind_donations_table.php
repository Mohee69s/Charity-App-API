<?php

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use app\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('in_kind_donations', function (Blueprint $table) {
            $table->id();   
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Campaign::class)->nullable();
            $table->string('name');
            $table->text('description');
            $table->enum('status',['approved','pending','rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_kind_donations');
    }
};
