<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'locale_id' => $this->locale_id,
            'key' => $this->key,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'locale' => $this->whenLoaded('locale', fn () => [
                'id' => $this->locale->id,
                'code' => $this->locale->code,
                'name' => $this->locale->name,
            ]),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'description' => $tag->description,
                ];
            })->values()),
        ];
    }
}

