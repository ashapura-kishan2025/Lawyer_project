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
        Schema::create('department_role_permissions', function (Blueprint $table) {
            $table->id();  // A unique auto-incrementing ID (if needed)
            $table->unsignedBigInteger('role_id');  // Foreign key for roles
            $table->unsignedBigInteger('permission_id');  // Foreign key for permissions
            $table->unsignedBigInteger('department_id');  // Foreign key for departments
            
            // $table->timestamps();  // For created_at and updated_at

            // Foreign key constraints
            // $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            // $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_role_permissions');
    }
};
