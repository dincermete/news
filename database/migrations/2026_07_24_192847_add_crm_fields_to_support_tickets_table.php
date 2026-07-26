<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table): void {
            $table->string('priority')->default('normal')->after('status');
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
            $table->timestamp('last_replied_at')->nullable()->after('chatbot_conversation_id');
            $table->timestamp('closed_at')->nullable()->after('last_replied_at');
            $table->index('priority');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn(['priority', 'last_replied_at', 'closed_at']);
        });
    }
};
