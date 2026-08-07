<?php

namespace App\Console\Commands;

use App\Services\ProductPublicUrl;
use App\Services\WooCommerceSiteImporter;
use Illuminate\Console\Command;

class ImportWooCommerceSitesCommand extends Command
{
    protected $signature = 'sites:import-woocommerce
        {path : WooCommerce ürün CSV yolu}
        {--dry-run : Veritabanına yazmadan rapor üret}
        {--no-media : Logo/görsel indirme}';

    protected $description = 'WooCommerce ürün CSV\'sinden Site + PromotionalListing aktarır';

    public function handle(): int
    {
        $path = $this->argument('path');
        $dryRun = (bool) $this->option('dry-run');
        $downloadMedia = ! (bool) $this->option('no-media');

        if (! is_readable($path)) {
            $this->error("Dosya okunamadı: {$path}");

            return self::FAILURE;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '').'Import başlıyor: '.$path);

        $importer = new WooCommerceSiteImporter(
            publicUrls: app(ProductPublicUrl::class),
            dryRun: $dryRun,
            downloadMedia: $downloadMedia && ! $dryRun,
        );

        $stats = $importer->import($path);

        $this->table(
            ['Metrik', 'Değer'],
            collect($stats)->map(fn ($value, $key): array => [$key, $value])->values()->all(),
        );

        $this->info($dryRun ? 'Dry-run tamamlandı.' : 'Import tamamlandı.');

        return self::SUCCESS;
    }
}
