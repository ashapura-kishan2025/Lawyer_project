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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');  // Module name (e.g., 'Client', 'Quotation', etc.)
            // $table->unsignedBigInteger('client_id');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade'); // Client ID
            $table->string('action');  // Action (create, update, delete)
            $table->text('data');  // Data changed (old or new data in JSON)
            $table->unsignedBigInteger('user_id')->nullable();  // ID of the user performing the action
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
