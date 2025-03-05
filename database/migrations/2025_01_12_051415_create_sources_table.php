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
    Schema::create('sources', function (Blueprint $table) {
      $table->id();
      $table->text('title'); // Source title (e.g., Website, Lex Mundi, Direct, etc.)
      // Timestamps
      $table->softDeletes(); // Soft delete column
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sources');
  }
};
