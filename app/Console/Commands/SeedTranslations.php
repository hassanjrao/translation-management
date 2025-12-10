<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeedTranslations extends Command
{
    protected $signature = 'translations:seed {--count=100000} {--batch=1000}';

    protected $description = 'Seed locales, tags, and translations with efficient batch inserts';

    public function handle(): int
    {

        $count = (int) $this->option('count');
        $batchSize = (int) $this->option('batch');

        $this->info("Seeding {$count} translations in batches of {$batchSize}...");
        $this->seedLocalesAndTags();

        $faker = \Faker\Factory::create();
        $locales = Locale::all();
        $tags = Tag::all()->pluck('id')->all();

        $inserted = 0;

        while ($inserted < $count) {
            $batch = [];
            $pivotBatch = [];
            $now = Carbon::now();
            $itemsThisBatch = min($batchSize, $count - $inserted);

            for ($i = 0; $i < $itemsThisBatch; $i++) {
                $key = 'key_' . ($inserted + $i + 1) . '_' . $faker->lexify('??????');
                $batch[] = [
                    'locale_id' => $locales->random()->id,
                    'key' => $key,
                    'value' => $faker->sentence(6),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('translations')->insert($batch);

            // MySQL returns the first auto-increment ID for the bulk insert.
            $firstId = (int) DB::getPdo()->lastInsertId();
            $lastId = $firstId + $itemsThisBatch - 1;
            for ($translationId = $firstId; $translationId <= $lastId; $translationId++) {
                $randomTags = collect($tags)->random(rand(1, 2))->all();
                foreach ($randomTags as $tagId) {
                    $pivotBatch[] = [
                        'translation_id' => $translationId,
                        'tag_id' => $tagId,
                    ];
                }
            }

            if ($pivotBatch) {
                DB::table('translation_tag')->insert($pivotBatch);
            }

            $inserted += $itemsThisBatch;
            $this->info("Inserted {$inserted}/{$count} translations...");
        }

        $this->info('Seeding complete.');
        return self::SUCCESS;
    }

    private function seedLocalesAndTags(): void
    {
        $locales = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
        ];

        foreach ($locales as $locale) {
            Locale::query()->firstOrCreate(['code' => $locale['code']], $locale + ['is_active' => true]);
        }

        $tags = ['mobile', 'desktop', 'web', 'admin', 'public'];
        foreach ($tags as $tag) {
            Tag::query()->firstOrCreate(['name' => $tag], ['description' => ucfirst($tag)]);
        }
    }
}
