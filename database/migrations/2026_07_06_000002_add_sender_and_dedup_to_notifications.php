<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add sender tracking and dedup index to notifications.
     * The unique index (user_id, message_id, type) prevents the same
     * notification from being inserted twice (e.g. by a queue retry).
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->json('data')->nullable()->after('message_id');

            $table->index('sender_id');
        });

        // Use a non-unique index first to backfill, then make it unique in a
        // second migration. Here we add it directly because the table is small
        // and we accept that pre-existing duplicates must be cleaned first.
        \Illuminate\Support\Facades\DB::statement(
            'DELETE n1 FROM notifications n1
             INNER JOIN notifications n2
             ON n1.user_id = n2.user_id
                AND n1.message_id IS NOT NULL
                AND n1.message_id = n2.message_id
                AND n1.type = n2.type
                AND n1.id < n2.id
             WHERE n1.message_id IS NOT NULL'
        );

        \Illuminate\Support\Facades\DB::statement(
            'CREATE UNIQUE INDEX notifications_user_message_type_unique
             ON notifications (user_id, message_id, type)'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('DROP INDEX notifications_user_message_type_unique ON notifications');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['sender_id']);
            $table->dropConstrainedForeignId('sender_id');
            $table->dropColumn('data');
        });
    }
};
