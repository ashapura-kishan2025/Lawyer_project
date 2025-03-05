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
        //This is for assignment
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->enum('assignment_type', ['regular', 'timekeep', 'both']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('quote_id')->nullable();
            // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade'); // Foreign key for client_id
            // $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->integer('ledger')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('expiry_at')->default(now()->addDays(30));
            $table->softDeletes();
            $table->timestamps();
        });
        //This is for assignment task
        Schema::create('assignment_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->text('description');
            $table->integer('received_amount');
            $table->enum('status', ['notsent', 'unpaid', 'paid'])->default('notsent');
            $table->text('memo_number')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->integer('lkr_rate')->nullable();
            $table->boolean('work_in_progress')->default(true); 
            $table->softDeletes();
            $table->timestamps();
            
            // Add foreign key constraints
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
          });
        //This is for assignment timekeep
        Schema::create('assignment_timekeep', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('description');
            $table->text('memo_number')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('rate')->nullable();
            $table->integer('amount');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();
      
            // Add foreign key constraints
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        //This is for assignment users
        Schema::create('assignment_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('access_level', ['read', 'edit']);
            $table->softDeletes();
            $table->timestamps();
      
            // Add foreign key constraints
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
