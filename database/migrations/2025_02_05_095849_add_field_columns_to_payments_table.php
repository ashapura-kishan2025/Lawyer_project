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
        Schema::create('payments', function (Blueprint $table) {
            //
            $table->id();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->text('memo_number')->nullable();
            $table->date('received_at')->default(now());
            $table->text('description');
            $table->enum('type', ['normal', 'VAT', 'SVAT']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            // Add foreign key constraints
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
