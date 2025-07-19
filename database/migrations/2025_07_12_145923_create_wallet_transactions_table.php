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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount');
            $table->string('reference_id')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->integer('wallet_id')->nullable();

            $table->unique(['id'], 'wallet_transactions_pkey');
        });
        DB::statement("alter table \"wallet_transactions\" add column \"type\" wallet_transactions_type_enum not null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
