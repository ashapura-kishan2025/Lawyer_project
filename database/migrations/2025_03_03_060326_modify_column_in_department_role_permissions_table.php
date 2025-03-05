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
        Schema::table('department_role_permissions', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('role_id')->nullable()->change();  // Foreign key for roles
            $table->unsignedBigInteger('permission_id')->nullable()->change();  // Foreign key for permissions
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department_role_permissions', function (Blueprint $table) {
            //
        });
    }
};
