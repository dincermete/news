<?php

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\PruneStaleLiveSessions;
use App\Console\Commands\RefreshPublicStats;
use App\Console\Commands\VerifyPublishedLinks;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(VerifyPublishedLinks::class)->daily();
Schedule::command(GenerateSitemap::class)->daily();
Schedule::command(PruneStaleLiveSessions::class)->everyMinute();
Schedule::command(RefreshPublicStats::class)->everyFiveMinutes();
