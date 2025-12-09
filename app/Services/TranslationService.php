<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TranslationDTO;
use App\DTOs\TranslationFilterDTO;
use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TranslationService
{
    public function __construct(private readonly TranslationRepositoryInterface $translations)
    {
    }

    public function create(TranslationDTO $dto): Translation
    {
        return DB::transaction(function () use ($dto): Translation {
            return $this->translations->create($dto);
        });
    }

    public function update(int $id, TranslationDTO $dto): Translation
    {
        return DB::transaction(function () use ($id, $dto): Translation {
            return $this->translations->update($id, $dto);
        });
    }

    public function search(TranslationFilterDTO $filter): LengthAwarePaginator
    {
        return $this->translations->search($filter);
    }

    public function exportByLocale(string $localeCode): Collection
    {
        return $this->translations->exportByLocale($localeCode);
    }

    public function delete(Translation $translation): bool
    {
        try {
            return DB::transaction(fn () => $this->translations->delete($translation));
        } catch (Throwable $exception) {
            Log::error('Failed to delete translation', ['id' => $translation->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}

