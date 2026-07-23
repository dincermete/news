<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('tax_id');
            $table->string('company_name')->nullable();
            $table->text('address');
            $table->string('tax_office')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('billing_profile_id')
                ->references('id')
                ->on('billing_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['billing_profile_id']);
        });

        Schema::dropIfExists('billing_profiles');
    }
};
