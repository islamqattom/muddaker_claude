<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add device tracking and soft-delete fields to push_subscriptions.
     * A user may have multiple subscriptions (phone + desktop + tablet),
     * so we need to track each device independently and support disabling.
     */
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('user_agent', 512)->nullable()->after('key_auth');
            $table->string('device_name')->nullable()->after('user_agent');
            $table->timestamp('last_used_at')->nullable()->after('expires_at');
            $table->boolean('is_active')->default(true)->after('last_used_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['user_agent', 'device_name', 'last_used_at', 'is_active']);
        });
    }
};
