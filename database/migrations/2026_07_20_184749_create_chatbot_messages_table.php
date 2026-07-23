<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('session_token')->index();
            $table->string('role');
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['chatbot_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
