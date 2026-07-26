<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_staff')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['support_ticket_id', 'created_at']);
        });

        $tickets = DB::table('support_tickets')
            ->whereNotNull('body')
            ->where('body', '!=', '')
            ->orderBy('id')
            ->get(['id', 'user_id', 'body', 'created_at']);

        foreach ($tickets as $ticket) {
            $exists = DB::table('support_ticket_messages')
                ->where('support_ticket_id', $ticket->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('support_ticket_messages')->insert([
                'support_ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'body' => $ticket->body,
                'is_staff' => false,
                'created_at' => $ticket->created_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
