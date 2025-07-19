<?php

use App\Models\Campaign;
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
        Schema::create('campaign_voulnteers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Campaign::class);
            $table->integer('goal');
            $table->integer('cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_voulnteers');
    }
};
