<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocaleFactory extends Factory
{
    protected $model = Locale::class;

    public function definition(): array
    {
        $code = $this->faker->unique()->languageCode();

        return [
            'code' => Str::lower($code),
            'name' => $this->faker->languageCode(),
            'is_active' => true,
        ];
    }
}

