<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    // Create quotes table
    Schema::create('quotes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id');
      $table->string('reference', 6)->nullable(); // Random 6-letter alpha word
      $table->date('expiry_at')->default(now()->addDays(30));
      $table->enum('status', ['quoted', 'awarded', 'lost']);
      $table->text('description')->nullable();
      $table->unsignedBigInteger('assignment_id')->nullable();
      $table->unsignedBigInteger('created_by');
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->softDeletes();
      $table->timestamps();

      // Add foreign key constraints
      $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
    });

    // Create quote_tasks table
    Schema::create('quote_tasks', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quote_id');
      $table->integer('amount');
      $table->unsignedBigInteger('currency_id');
      $table->text('description');
      $table->unsignedBigInteger('department_id');
      $table->softDeletes();
      $table->timestamps();

      // Add foreign key constraints
      $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
      $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
      $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
    });

    // Create quote_users table
    Schema::create('quote_users', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quote_id');
      $table->unsignedBigInteger('user_id');
      $table->enum('access_level', ['read', 'edit']);
      $table->softDeletes();
      $table->timestamps();

      // Add foreign key constraints
      $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('quote_users');
    Schema::dropIfExists('quote_tasks');
    Schema::dropIfExists('quotes');
  }
};
