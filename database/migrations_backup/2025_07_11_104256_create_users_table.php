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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name');
            
            $table->string('email')->unique('uq_97672ac88f789774dd47f7c8be3');
            $table->string('password_hash');
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->boolean('has_wallet')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));

            $table->unique(['email'], 'users_email_key');
            $table->unique(['id'], 'users_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
