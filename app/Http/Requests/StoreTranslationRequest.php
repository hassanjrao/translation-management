<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $localeId = $this->getLocaleId();

        return [
            'locale' => ['required', 'string', Rule::exists('locales', 'code')],
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('translations', 'key')->where(static fn ($query) => $query->where('locale_id', $localeId)),
            ],
            'value' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => [Rule::exists('tags', 'name')],
        ];
    }

    private function getLocaleId(): ?int
    {
        return Locale::query()
            ->where('code', (string) $this->input('locale'))
            ->value('id');
    }
}

