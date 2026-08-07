<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Province;
use App\Models\Site;
use App\Support\DomainProvinceMatcher;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitesAssignProvincesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProvinceSeeder::class);
    }

    public function test_matcher_extracts_province_from_domain(): void
    {
        $matcher = new DomainProvinceMatcher;
        $provinces = Province::query()->get();

        $elazigId = Province::query()->where('slug', 'elazig')->value('id');
        $bursaId = Province::query()->where('slug', 'bursa')->value('id');
        $antepId = Province::query()->where('slug', 'gaziantep')->value('id');

        $this->assertSame([$elazigId], $matcher->matchIds('elazigdahaber.com', $provinces));
        $this->assertSame([$bursaId], $matcher->matchIds('bursadabugun.com', $provinces));
        $this->assertSame([$antepId], $matcher->matchIds('gaziantephaberler.com', $provinces));
        $this->assertSame([$antepId], $matcher->matchIds('antephaber.com', $provinces));
        $this->assertSame([], $matcher->matchIds('turkmustafa.com', $provinces));
        $this->assertSame([], $matcher->matchIds('kamusal.net', $provinces));
    }

    public function test_command_assigns_from_domain_and_replaces_blanket_links(): void
    {
        $elazig = Province::query()->where('slug', 'elazig')->firstOrFail();
        $istanbul = Province::query()->where('slug', 'istanbul')->firstOrFail();

        $matched = Site::factory()->create([
            'status' => SiteStatus::Active,
            'domain' => 'elazigdahaber.com',
        ]);
        $unmatched = Site::factory()->create([
            'status' => SiteStatus::Active,
            'domain' => 'genelhaberportali.com',
        ]);

        // Simulate previous blanket assignment
        DB::table('province_site')->insert([
            ['province_id' => $elazig->id, 'site_id' => $matched->id],
            ['province_id' => $istanbul->id, 'site_id' => $matched->id],
            ['province_id' => $istanbul->id, 'site_id' => $unmatched->id],
        ]);

        $this->artisan('sites:assign-provinces')->assertSuccessful();

        $this->assertSame([$elazig->id], $matched->provinces()->pluck('provinces.id')->all());
        $this->assertSame(0, $unmatched->provinces()->count());
        $this->assertSame(1, (int) DB::table('province_site')->count());
    }

    public function test_dry_run_does_not_write(): void
    {
        Site::factory()->create([
            'status' => SiteStatus::Active,
            'domain' => 'ankaragundem.com.tr',
        ]);

        $this->artisan('sites:assign-provinces', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame(0, (int) DB::table('province_site')->count());
    }
}
