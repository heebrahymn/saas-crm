<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->enum('role', ['admin', 'manager', 'staff'])->default('staff');
            $table->timestamps();
            
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};