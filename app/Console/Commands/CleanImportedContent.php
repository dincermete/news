<?php

namespace App\Console\Commands;

use App\Models\PromotionalListing;
use App\Models\Site;
use App\Services\ImportedContentCleaner;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CleanImportedContent extends Command
{
    protected $signature = 'content:clean-imported
        {--dry-run : Kaydetmeden kaç kayıt değişeceğini raporla}';

    protected $description = 'İçe aktarılan site ve ilan metinlerindeki kaçış dizilerini ve editör HTML çöplerini temizler';

    public function handle(ImportedContentCleaner $cleaner): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $sites = $this->cleanSites($cleaner, $dryRun);
        $listings = $this->cleanListings($cleaner, $dryRun);

        $this->table(
            ['Tablo', 'Güncellenen kayıt'],
            [['sites', $sites], ['promotional_listings', $listings]],
        );

        $this->info($dryRun ? 'Dry-run tamamlandı.' : 'Temizlik tamamlandı.');

        return self::SUCCESS;
    }

    protected function cleanSites(ImportedContentCleaner $cleaner, bool $dryRun): int
    {
        $updated = 0;

        Site::query()->chunkById(200, function (Collection $chunk) use ($cleaner, $dryRun, &$updated): void {
            foreach ($chunk as $site) {
                $changes = array_filter([
                    'short_description' => $this->diff($site->short_description, $cleaner->plainText($site->short_description)),
                    'description' => $this->diff($site->description, $cleaner->richText($site->description)),
                ], fn (?string $value): bool => $value !== null);

                if ($changes === []) {
                    continue;
                }

                $updated++;

                if (! $dryRun) {
                    $site->forceFill($changes)->saveQuietly();
                }
            }
        });

        return $updated;
    }

    protected function cleanListings(ImportedContentCleaner $cleaner, bool $dryRun): int
    {
        $updated = 0;

        PromotionalListing::query()->chunkById(200, function (Collection $chunk) use ($cleaner, $dryRun, &$updated): void {
            foreach ($chunk as $listing) {
                $changes = array_filter([
                    'short_description' => $this->diff($listing->short_description, $cleaner->plainText($listing->short_description)),
                    'description' => $this->diff($listing->description, $cleaner->richText($listing->description)),
                    'delivery_details' => $this->diff($listing->delivery_details, $cleaner->richText($listing->delivery_details)),
                    'meta_description' => $this->diff($listing->meta_description, $cleaner->plainText($listing->meta_description, 512)),
                ], fn (?string $value): bool => $value !== null);

                if ($changes === []) {
                    continue;
                }

                $updated++;

                if (! $dryRun) {
                    $listing->forceFill($changes)->saveQuietly();
                }
            }
        });

        return $updated;
    }

    /**
     * Returns the cleaned value only when it actually differs from the stored one.
     */
    protected function diff(?string $current, string $cleaned): ?string
    {
        if (! filled($current)) {
            return null;
        }

        return $cleaned === $current ? null : $cleaned;
    }
}
