<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accountName = 'NEWSTANITIM YAZILIM REKLAM TİCARET LİMİTED ŞİRKETİ';

        $banks = [
            ['name' => 'QNB', 'short_code' => 'QNB', 'iban' => 'TR57 0011 1000 0000 0113 6183 95', 'sort_order' => 1],
            ['name' => 'Garanti BBVA', 'short_code' => 'GAR', 'iban' => 'TR21 0006 2000 6050 0006 2932 00', 'sort_order' => 2],
            ['name' => 'Ziraat Bankası', 'short_code' => 'ZIR', 'iban' => 'TR13 0001 0026 3357 4280 0550 03', 'sort_order' => 3],
            ['name' => 'TEB', 'short_code' => 'TEB', 'iban' => 'TR15 0003 2000 0000 0028 3659 43', 'sort_order' => 4],
            ['name' => 'Kuveyt Türk', 'short_code' => 'KUV', 'iban' => 'TR25 0020 5000 0977 6652 5000 01', 'sort_order' => 5],
            ['name' => 'Yapı Kredi', 'short_code' => 'YAP', 'iban' => 'TR36 0006 7010 0000 0054 1024 61', 'sort_order' => 6],
            ['name' => 'Akbank', 'short_code' => 'AKB', 'iban' => 'TR60 0004 6007 4788 8000 2337 95', 'sort_order' => 7],
            ['name' => 'İş Bankası', 'short_code' => 'İşB', 'iban' => 'TR92 0006 4000 0016 6090 3521 43', 'sort_order' => 8],
        ];

        foreach ($banks as $bank) {
            BankAccount::query()->updateOrCreate(
                ['iban' => $bank['iban']],
                [
                    'name' => $bank['name'],
                    'short_code' => $bank['short_code'],
                    'account_name' => $accountName,
                    'sort_order' => $bank['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
