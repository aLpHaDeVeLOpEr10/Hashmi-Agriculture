<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('location', 200)->nullable();
            $table->char('currency', 3)->default('PKR');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('phone', 20)->unique();
            $table->enum('role', ['owner', 'manager', 'data_entry', 'viewer'])->default('data_entry');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->string('locale', 5)->default('en');
            $table->rememberToken();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('farms', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::table('farms', fn (Blueprint $table) => $table->dropForeign(['created_by']));
        Schema::dropIfExists('users');
        Schema::dropIfExists('farms');
    }
};
