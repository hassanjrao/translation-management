<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Translation;
use Illuminate\Http\Request;

/**
 * Data transfer object for translation payloads.
 */
final readonly class TranslationDTO
{
    public function __construct(
        public string $localeCode,
        public string $key,
        public string $value,
        public array $tags = []
    ) {
    }

    /**
     * Build DTO from an incoming HTTP request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            localeCode: (string) $request->input('locale'),
            key: (string) $request->input('key'),
            value: (string) $request->input('value'),
            tags: $request->input('tags', [])
        );
    }

    /**
     * Build DTO for updates while preserving existing values.
     */
    public static function fromRequestForUpdate(Request $request, Translation $existing): self
    {
        return new self(
            localeCode: (string) $request->input('locale', $existing->locale ? $existing->locale->code : null),
            key: (string) $request->input('key', $existing->key),
            value: (string) $request->input('value', $existing->value),
            tags: $request->input('tags', $existing->tags->pluck('name')->all())
        );
    }

    public function toArray(): array
    {
        return [
            'locale' => $this->localeCode,
            'key' => $this->key,
            'value' => $this->value,
            'tags' => $this->tags,
        ];
    }
}

