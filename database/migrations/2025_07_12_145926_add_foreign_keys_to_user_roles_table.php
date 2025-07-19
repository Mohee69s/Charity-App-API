<?php

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
        Schema::table('user_roles', function (Blueprint $table) {
            $table->foreign(['user_id'], 'FK_87b8888186ca9769c960e926870')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['role_id'], 'FK_b23c65e50a758245a33ee35fda1')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropForeign('FK_87b8888186ca9769c960e926870');
            $table->dropForeign('FK_b23c65e50a758245a33ee35fda1');
        });
    }
};
