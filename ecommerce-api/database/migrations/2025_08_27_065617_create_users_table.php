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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('email', 200)->unique();
            $table->string('gender', 10);
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('email_verify')->default(false);
            $table->string('image');
            $table->string('phone', 15);
            $table->string('dob')->comment('date of birth');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->foreignId('role_id')
                ->constrained('roles');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
