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
        Schema::create('request_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_path');
            $table->timestamp('uploaded_at')->nullable()->default(DB::raw("now()"));
            $table->integer('request_id')->nullable();

            $table->unique(['id'], 'request_documents_pkey');
        });
        DB::statement("alter table \"request_documents\" add column \"type\" request_documents_type_enum not null default 'id_card'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_documents');
    }
};
