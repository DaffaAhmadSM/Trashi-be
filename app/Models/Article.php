<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['author_id', 'title', 'content', 'created_at', 'is_published', 'thumbnail_image', 'full_image', 'uuid'])]
class Article extends Model
{
    protected $primaryKey = 'article_id';

    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::creating(function (Article $article): void {
            $article->uuid = (string) Str::orderedUuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
