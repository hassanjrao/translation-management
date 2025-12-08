<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $translationId = (int) $this->route('id');
        $localeId = $this->getLocaleId();

        $keyRule = Rule::unique('translations', 'key')->ignore($translationId);
        if ($localeId !== null) {
            $keyRule = $keyRule->where(static fn ($query) => $query->where('locale_id', $localeId));
        }

        return [
            'locale' => ['sometimes', 'string', Rule::exists('locales', 'code')],
            'key' => ['sometimes', 'string', 'max:255', $keyRule],
            'value' => ['sometimes', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => [Rule::exists('tags', 'name')],
        ];
    }

    private function getLocaleId(): ?int
    {
        if (!$this->filled('locale')) {
            return null;
        }

        return Locale::query()
            ->where('code', (string) $this->input('locale'))
            ->value('id');
    }
}

