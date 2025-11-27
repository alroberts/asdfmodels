<?php

namespace App\Services;

use App\Models\GeoNameLocation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class GeoNamesImporter
{
    public function import(
        string $path,
        bool $truncate = false,
        ?int $limit = null,
        ?OutputInterface $output = null
    ): int {
        if (! is_file($path)) {
            throw new \InvalidArgumentException("GeoNames file not found at {$path}");
        }

        if ($truncate) {
            DB::table((new GeoNameLocation())->getTable())->truncate();
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to open GeoNames file at {$path}");
        }

        DB::disableQueryLog();

        $processed = 0;
        $batch = [];
        $batchSize = 1000;
        $now = now();

        while (($line = fgets($handle)) !== false) {
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $columns = explode("\t", trim($line));

            if (count($columns) < 19) {
                continue;
            }

            $batch[] = $this->mapRow($columns, $now);
            $processed++;

            if (count($batch) >= $batchSize) {
                $this->writeBatch($batch);
                $batch = [];
                $this->report($output, $processed);
            }

            if ($limit !== null && $processed >= $limit) {
                break;
            }
        }

        if (! empty($batch)) {
            $this->writeBatch($batch);
            $this->report($output, $processed);
        }

        fclose($handle);

        return $processed;
    }

    protected function mapRow(array $columns, $timestamp): array
    {
        return [
            'geoname_id' => (int) $columns[0],
            'name' => Str::limit($columns[1], 200, ''),
            'ascii_name' => $columns[2] !== '' ? Str::limit($columns[2], 200, '') : null,
            'alternate_names' => $columns[3] !== '' ? $columns[3] : null,
            'latitude' => (float) $columns[4],
            'longitude' => (float) $columns[5],
            'feature_class' => $columns[6] !== '' ? $columns[6][0] : null,
            'feature_code' => $columns[7] !== '' ? Str::limit($columns[7], 10, '') : null,
            'country_code' => strtoupper(Str::limit($columns[8], 2, '')),
            'admin1_code' => $columns[10] !== '' ? Str::limit($columns[10], 20, '') : null,
            'admin2_code' => $columns[11] !== '' ? Str::limit($columns[11], 80, '') : null,
            'population' => max((int) $columns[14], 0),
            'timezone' => $columns[17] !== '' ? Str::limit($columns[17], 40, '') : null,
            'modification_date' => $this->parseDate($columns[18]),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    protected function writeBatch(array $batch): void
    {
        DB::table((new GeoNameLocation())->getTable())->upsert(
            $batch,
            ['geoname_id'],
            [
                'name',
                'ascii_name',
                'alternate_names',
                'latitude',
                'longitude',
                'feature_class',
                'feature_code',
                'country_code',
                'admin1_code',
                'admin2_code',
                'population',
                'timezone',
                'modification_date',
                'updated_at',
            ]
        );
    }

    protected function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $date = CarbonImmutable::createFromFormat('Y-m-d', $value);

        return $date?->toDateString();
    }

    protected function report(?OutputInterface $output, int $processed): void
    {
        if ($output) {
            $output->writeln("Processed {$processed} rows...");
        }
    }
}


