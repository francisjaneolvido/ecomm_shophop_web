<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Archive dormant, safely emptied branches; referential records are never physically deleted.
Artisan::command('branches:archive-due', function (\App\Services\BranchLifecycleService $lifecycle) {
    $archived = 0;
    $skipped = 0;
    \App\Models\Logistics\SortingCenter::where('is_main', false)
        ->where('status', 'inactive')->whereNull('archived_at')
        ->whereNotNull('deactivated_at')
        ->where('deactivated_at', '<=', now()->subDays(\App\Services\BranchLifecycleService::ARCHIVE_AFTER_DAYS))
        ->orderBy('id')->pluck('id')->each(function ($id) use ($lifecycle, &$archived, &$skipped) {
            if ($lifecycle->archive((int) $id, null, true)) $archived++;
            else $skipped++;
        });
    $this->info("Archived {$archived} inactive branches; {$skipped} blocked/revalidated.");
})->purpose('Soft-archive eligible branches after 30 days without deleting history');
Schedule::command('branches:archive-due')->dailyAt('02:30')->withoutOverlapping();