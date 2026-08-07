<?php

namespace App\Console\Commands;

use App\Services\AssignProvincesToSites;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sites:assign-provinces
    {--all-sites : Pasif siteleri de dahil et}
    {--keep-existing : Mevcut bağları silme; üzerine ekle}
    {--dry-run : Sadece eşleşmeleri göster, yazma}')]
#[Description('Domain adından il çıkarıp siteleri illere bağlar (örn. elazigdahaber.com → Elazığ).')]
class SitesAssignProvincesCommand extends Command
{
    public function handle(AssignProvincesToSites $assigner): int
    {
        $activeOnly = ! $this->option('all-sites');
        $replace = ! $this->option('keep-existing');
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? 'Domain eşleştirmesi (dry-run)…'
            : 'Domain eşleştirmesi yazılıyor…');

        $result = $assigner->fromDomains(
            activeOnly: $activeOnly,
            replace: $replace,
            dryRun: $dryRun,
        );

        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Taranan site', (string) $result['sites']],
                ['İl eşleşen site', (string) $result['matched_sites']],
                ['Eşleşmeyen site', (string) $result['unmatched']],
                ['province_site satırı', (string) $result['links']],
                ['Mod', $replace ? 'replace (eski bağlar silindi)' : 'keep-existing'],
            ],
        );

        if ($dryRun) {
            $this->warn('Dry-run: veritabanına yazılmadı.');
        } else {
            $this->info('Tamam.');
        }

        return self::SUCCESS;
    }
}
