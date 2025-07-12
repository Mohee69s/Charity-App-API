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
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unique('uq_92558c08091598f7a4439586cda');
            $table->decimal('balance')->nullable()->default(0);
            $table->string('wallet_pin')->nullable();
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));

            $table->unique(['id'], 'wallets_pkey');
            $table->unique(['user_id'], 'wallets_user_id_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
