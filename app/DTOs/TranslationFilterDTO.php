<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Http\Request;

/**
 * Filters for translation search operations.
 */
final readonly class TranslationFilterDTO
{
    public function __construct(
        public ?string $localeCode = null,
        public ?string $key = null,
        public ?string $value = null,
        public array $tags = [],
        public int $perPage = 15
    ) {
    }

    /**
     * Build filter DTO from request.
     */
    public static function fromRequest(Request $request): self
    {
        $tags = [];
        if ($request->filled('tags')) {
            $tags = array_filter(array_map('trim', explode(',', (string) $request->input('tags'))));
        }

        return new self(
            localeCode: $request->input('locale'),
            key: $request->input('key'),
            value: $request->input('value'),
            tags: $tags,
            perPage: (int) $request->input('per_page', 15)
        );
    }

    public function toArray(): array
    {
        return [
            'locale' => $this->localeCode,
            'key' => $this->key,
            'value' => $this->value,
            'tags' => $this->tags,
            'per_page' => $this->perPage,
        ];
    }
}

