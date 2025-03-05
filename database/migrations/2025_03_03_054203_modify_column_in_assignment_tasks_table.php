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
        Schema::table('assignment_tasks', function (Blueprint $table) {
            //
            $table->text('description')->nullable()->change();
            $table->integer('received_amount')->nullable()->change();
            $table->unsignedBigInteger('assignment_id')->nullable()->change();
            $table->unsignedBigInteger('currency_id')->nullable()->change();
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->text('memo_number')->nullable()->change();
            $table->integer('lkr_rate')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_tasks', function (Blueprint $table) {
            //
        });
    }
};
