<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
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

        $tagNames = ['mobile', 'desktop', 'web', 'admin', 'public'];
        foreach ($tagNames as $tag) {
            Tag::query()->firstOrCreate(['name' => $tag], ['description' => ucfirst($tag)]);
        }

        // Seed a manageable default dataset; heavy seeding is handled via the console command.
        Translation::factory()
            ->count(100)
            ->create()
            ->each(function (Translation $translation) use ($tagNames): void {
                $translation->tags()->sync(
                    Tag::query()->whereIn('name', collect($tagNames)->random(rand(1, 2))->all())->pluck('id')->all()
                );
            });
    }
}

