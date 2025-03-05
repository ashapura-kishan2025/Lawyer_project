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
    Schema::create('clients', function (Blueprint $table) {

      $table->id(); // Primary Key
      $table->string('client')->nullable(); // Client name (company or individual)
      $table->string('email')->unique(); // Required and unique
      $table->text('billing_address')->nullable(); // Billing address
      $table->enum('type', ['company', 'individual']); // Enum type
      $table->string('contact_person')->nullable(); // Contact person
      $table->string('mobile')->nullable(); // Optional mobile
      $table->unsignedBigInteger('currency_id'); // Foreign key to currency table
      $table->unsignedBigInteger('source_id'); // Foreign key to sources table
      $table->text('source_other')->nullable(); // Text box if source_id is 1
      $table->unsignedBigInteger('country_id')->default(1); // Foreign key to country table, default Sri Lanka (assuming 1 is Sri Lanka)
      $table->unsignedBigInteger('created_by'); // Foreign key to users table
      $table->text('linkedin_url')->nullable(); // Optional LinkedIn URL
      $table->text('website_url')->nullable(); // Optional website URL
      $table->softDeletes(); // Soft delete column

      // Timestamps
      $table->timestamps();

      // Foreign Key Constraints
      $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
      $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
      $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clients');
  }
};
