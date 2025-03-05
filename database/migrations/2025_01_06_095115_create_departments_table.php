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
    Schema::create('departments', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->text('description');
      $table->enum('status', [0, 1])->default(1); // 0 - Inactive, 1 - Active
      $table->softDeletes(); // Soft delete column
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('departments');
  }
};
