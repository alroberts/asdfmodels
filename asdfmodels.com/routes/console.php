<?php

use App\Services\GeoNamesImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('geonames:import {--path=} {--truncate} {--limit=}', function (GeoNamesImporter $importer) {
    $path = $this->option('path') ?? storage_path('app/geonames/cities500.txt');
    $truncate = (bool) $this->option('truncate');
    $limit = $this->option('limit');
    $limit = $limit !== null ? (int) $limit : null;

    try {
        $count = $importer->import($path, $truncate, $limit, $this->output);
        $this->info("Imported {$count} GeoNames records.");
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return 1;
    }
})->purpose('Import GeoNames city data from the downloaded dataset');
