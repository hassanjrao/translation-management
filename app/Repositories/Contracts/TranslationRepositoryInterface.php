<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\TranslationDTO;
use App\DTOs\TranslationFilterDTO;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TranslationRepositoryInterface
{
    public function create(TranslationDTO $dto): Translation;

    public function update(int $id, TranslationDTO $dto): Translation;

    public function findById(int $id): ?Translation;

    public function search(TranslationFilterDTO $filter): LengthAwarePaginator;

    public function exportByLocale(string $localeCode): Collection;

    public function delete(int $id): bool;
}
