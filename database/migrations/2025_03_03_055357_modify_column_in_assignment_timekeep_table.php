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
        Schema::table('assignment_timekeep', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('assignment_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->text('description')->change();
            $table->text('memo_number')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->integer('rate')->nullable()->change();
            $table->integer('amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_timekeep', function (Blueprint $table) {
            //
        });
    }
};
