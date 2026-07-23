<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreignId('order_group_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_group_id');
            $table->dropForeign(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }
};
