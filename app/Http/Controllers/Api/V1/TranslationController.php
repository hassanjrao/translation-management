<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\TranslationDTO;
use App\DTOs\TranslationFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchTranslationRequest;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Models\Translation;
use App\Services\TranslationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TranslationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function store(StoreTranslationRequest $request): JsonResponse
    {
        try {
            $translation = $this->translationService->create(TranslationDTO::fromRequest($request));

            return $this->successResponse($translation, 'Translation created', Response::HTTP_CREATED);
        } catch (Throwable $exception) {
            return $this->errorResponse('Unable to create translation', ['exception' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(UpdateTranslationRequest $request, Translation $translation): JsonResponse
    {
        try {
            $dto = TranslationDTO::fromRequestForUpdate($request, $translation);
            $updated = $this->translationService->update($translation->id, $dto);

            return $this->successResponse($updated, 'Translation updated');
        } catch (Throwable $exception) {
            return $this->errorResponse('Unable to update translation', ['exception' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(int $id): JsonResponse
    {
        $translation = $this->translationService->findById($id);
        if (!$translation) {
            return $this->errorResponse('Translation not found', [], Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($translation, 'Translation retrieved');
    }

    public function search(SearchTranslationRequest $request): JsonResponse
    {
        $paginator = $this->translationService->search(TranslationFilterDTO::fromRequest($request));

        return $this->successResponse($paginator, 'Translations fetched');
    }

    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => ['required', 'string', 'exists:locales,code'],
        ]);

        $data = $this->translationService->exportByLocale((string) $request->input('locale'));

        return $this->successResponse($data, 'Translations exported');
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->translationService->delete($id);
            return $this->successResponse(true, 'Translation deleted');
        } catch (Throwable $exception) {
            return $this->errorResponse('Unable to delete translation', ['exception' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
