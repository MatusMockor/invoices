<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    private const TOTAL_COMPANIES = 100_000;

    private const BATCH_SIZE = 1_000;

    private const PROGRESS_INTERVAL = 10_000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to generate '.number_format(self::TOTAL_COMPANIES).' companies...');

        $startTime = microtime(true);
        $usedIcos = [];

        $batches = (int) ceil(self::TOTAL_COMPANIES / self::BATCH_SIZE);

        for ($batchIndex = 0; $batchIndex < $batches; $batchIndex++) {
            $batchData = [];
            $batchSize = min(self::BATCH_SIZE, self::TOTAL_COMPANIES - ($batchIndex * self::BATCH_SIZE));

            for ($i = 0; $i < $batchSize; $i++) {
                $ico = $this->generateUniqueIco($usedIcos);
                $usedIcos[$ico] = true;

                $batchData[] = $this->generateCompanyData($ico);
            }

            Company::insert($batchData);

            $processedCount = ($batchIndex + 1) * self::BATCH_SIZE;

            if ($processedCount % self::PROGRESS_INTERVAL === 0 || $processedCount >= self::TOTAL_COMPANIES) {
                $actualCount = min($processedCount, self::TOTAL_COMPANIES);
                $this->command->info("Generated {$actualCount} / ".self::TOTAL_COMPANIES.' companies');
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->command->info('Successfully generated '.number_format(self::TOTAL_COMPANIES)." companies in {$duration} seconds");
    }

    /**
     * Generate a unique ICO (company identification number).
     *
     * @param  array<string, bool>  $usedIcos
     */
    private function generateUniqueIco(array &$usedIcos): string
    {
        do {
            $ico = (string) fake()->unique()->numberBetween(10000000, 99999999);
        } while (isset($usedIcos[$ico]));

        return $ico;
    }

    /**
     * Generate company data for bulk insert.
     *
     * @return array<string, mixed>
     */
    private function generateCompanyData(string $ico): array
    {
        $now = Carbon::now();

        return [
            'name' => fake()->company(),
            'ico' => $ico,
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('#####'),
            'country' => 'Slovakia',
            'dic' => fake()->numerify('##########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'registration_office' => fake()->randomElement([
                'Okresný súd Bratislava I',
                'Okresný súd Košice I',
                'Okresný súd Žilina',
                'Okresný súd Prešov',
                'Okresný súd Banská Bystrica',
            ]),
            'registration_number' => fake()->numerify('######/B'),
            'type' => fake()->randomElement(['s.r.o.', 'a.s.', 'k.s.']),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
