<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->boolean('is_legal')->default(false)->after('is_system');
        });

        $legalSlugs = [
            'mesafeli-satis-sozlesmesi',
            'on-bilgilendirme-formu',
            'gizlilik',
            'kvkk',
            'cerez-politikasi',
            'uyelik-sozlesmesi',
        ];

        DB::table('pages')
            ->whereIn('slug', $legalSlugs)
            ->update(['is_legal' => true]);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('is_legal');
        });
    }
};
