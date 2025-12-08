<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale_id',
        'key',
        'value',
    ];

    protected $casts = [
        'locale_id' => 'integer',
    ];

    protected $with = ['locale', 'tags'];

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeByLocale(Builder $query, string $localeCode): Builder
    {
        return $query->whereHas('locale', static fn (Builder $builder) => $builder->where('code', $localeCode));
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', 'like', "%{$key}%");
    }

    public function scopeByValue(Builder $query, string $value): Builder
    {
        return $query->where('value', 'like', "%{$value}%");
    }

    public function scopeByTags(Builder $query, array $tags): Builder
    {
        if (empty($tags)) {
            return $query;
        }

        return $query->whereHas('tags', static fn (Builder $builder) => $builder->whereIn('name', $tags));
    }
}

