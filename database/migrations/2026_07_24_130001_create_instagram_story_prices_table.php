<?php

use App\Enums\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instagram_story_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
            $table->string('format');
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default(Currency::Try->value);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['instagram_account_id', 'format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_story_prices');
    }
};
