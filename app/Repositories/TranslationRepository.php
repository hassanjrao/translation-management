<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\TranslationDTO;
use App\DTOs\TranslationFilterDTO;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TranslationRepository implements TranslationRepositoryInterface
{
    private const CACHE_TTL_SECONDS = 3600;

    public function create(TranslationDTO $dto): Translation
    {
        $localeId = $this->getLocaleId($dto->localeCode);
        $translation = Translation::query()->create([
            'locale_id' => $localeId,
            'key' => $dto->key,
            'value' => $dto->value,
        ]);

        $translation->tags()->sync($this->getTagIds($dto->tags));
        $this->invalidateCache($dto->localeCode);

        return $translation->fresh();
    }

    public function update(int $id, TranslationDTO $dto): Translation
    {
        $translation = Translation::query()->findOrFail($id);
        $previousLocale = $translation->locale ? $translation->locale->code : null;

        if ($dto->localeCode !== ($translation->locale ? $translation->locale->code : null)) {
            $translation->locale_id = $this->getLocaleId($dto->localeCode);
        }

        $translation->fill([
            'key' => $dto->key,
            'value' => $dto->value,
        ]);
        $translation->save();
        $translation->tags()->sync($this->getTagIds($dto->tags));

        $this->invalidateCache($dto->localeCode);
        if ($previousLocale && $previousLocale !== $dto->localeCode) {
            $this->invalidateCache($previousLocale);
        }

        return $translation->fresh();
    }

    public function findById(int $id): ?Translation
    {
        return Translation::query()
            ->with(['locale', 'tags'])
            ->find($id);
    }

    public function search(TranslationFilterDTO $filter): LengthAwarePaginator
    {
        $cacheKey = $this->makeSearchCacheKey($filter);

        return Cache::tags(['translations', 'search'])
            ->remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($filter): LengthAwarePaginator {
                $query = Translation::query()
                    ->with([
                        'locale:id,code,name',
                        'tags:id,name,description',
                    ])
                    ->select(['id', 'locale_id', 'key', 'value', 'created_at', 'updated_at']);

                if ($filter->localeCode) {
                    $query->byLocale($filter->localeCode);
                }
                if ($filter->key) {
                    $query->byKey($filter->key);
                }
                if ($filter->value) {
                    $query->byValue($filter->value);
                }
                if ($filter->tags) {
                    $query->byTags($filter->tags);
                }

                return $query
                    ->orderByDesc('updated_at')
                    ->paginate($filter->perPage);
            });
    }

    public function exportByLocale(string $localeCode): Collection
    {
        $versionKey = "translations:version:{$localeCode}";
        $version = Cache::get($versionKey, 1);
        $cacheKey = "translations:{$localeCode}:{$version}";

        $translations = Cache::tags(['translations', "locale:{$localeCode}"])
        ->remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($localeCode) {
            $translations = Translation::query()
                ->byLocale($localeCode)
                ->select(['key', 'value'])
                ->cursor()
                ->mapWithKeys(fn ($t) => [$t->key => $t->value])
                ->all(); // store as plain array to avoid LazyCollection in cache

            return $translations;
        });

        return collect($translations);
    }

    public function delete(Translation $translation): bool
    {
        $localeCode = $translation->locale ? $translation->locale->code : null;
        $deleted = (bool) $translation->delete();

        if ($localeCode) {
            $this->invalidateCache($localeCode);
        }

        return $deleted;
    }

    private function getLocaleId(string $localeCode): int
    {
        $localeId = Locale::query()->where('code', $localeCode)->value('id');
        if ($localeId === null) {
            throw new \InvalidArgumentException("Locale {$localeCode} not found");
        }

        return (int) $localeId;
    }

    private function getTagIds(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return Tag::query()
            ->whereIn('name', $tags)
            ->pluck('id')
            ->all();
    }

    private function invalidateCache(string $localeCode): void
    {
        Cache::tags(['translations', "locale:{$localeCode}", 'search'])->flush();
    }

    private function makeSearchCacheKey(TranslationFilterDTO $filter): string
    {
        return 'translations.search.' . sha1((string) json_encode($filter->toArray()));
    }
}
