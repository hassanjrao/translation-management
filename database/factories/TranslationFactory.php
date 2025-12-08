<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'locale_id' => Locale::factory(),
            'key' => Str::slug($this->faker->unique()->sentence(3)),
            'value' => $this->faker->sentence(6),
        ];
    }
}

