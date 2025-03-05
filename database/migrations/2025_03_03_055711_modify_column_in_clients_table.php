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
        Schema::table('clients', function (Blueprint $table) {
            //
            $table->string('client')->nullable()->change(); // Client name (company or individual)
            $table->text('billing_address')->nullable()->change(); // Billing address
            $table->string('contact_person')->nullable()->change(); // Contact person
            $table->string('mobile')->nullable()->change(); // Optional mobile
            $table->unsignedBigInteger('source_id')->nullable()->change(); // Foreign key to sources table
            $table->text('source_other')->nullable()->nullable()->change(); // Text box if source_id is 1
            $table->text('linkedin_url')->nullable()->change(); // Optional LinkedIn URL
            $table->text('website_url')->nullable()->change(); 
            $table->string('company_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
