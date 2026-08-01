<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#d4a030');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('created_by');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
