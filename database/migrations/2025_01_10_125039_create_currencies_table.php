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
    Schema::create('currencies', function (Blueprint $table) {
      $table->id();
      $table->string('currency');
      $table->string('code');
      $table->enum('status', [0, 1])->default(1);
      $table->softDeletes(); // Soft delete column
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('currencies');
  }
};
