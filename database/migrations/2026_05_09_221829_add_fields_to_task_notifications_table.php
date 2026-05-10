<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('task_id')->index();
            $table->string('type')->index();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('task_notifications', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'task_id', 'type', 'is_read', 'read_at']);
        });
    }
};