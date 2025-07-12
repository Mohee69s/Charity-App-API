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
        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description')->nullable();
            $table->text('admin_response')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw("now()"));
            $table->timestamp('updated_at')->nullable()->default(DB::raw("now()"));
            $table->integer('user_id')->nullable();

            $table->unique(['id'], 'assistance_requests_pkey');
        });
        DB::statement("alter table \"assistance_requests\" add column \"status\" assistance_requests_status_enum null default 'pending'");
        DB::statement("alter table \"assistance_requests\" add column \"type\" assistance_requests_type_enum null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_requests');
    }
};
