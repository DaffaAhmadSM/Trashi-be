<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['author_id', 'title', 'content', 'created_at', 'is_published'])]
class Article extends Model
{
    protected $primaryKey = 'article_id';

    public const UPDATED_AT = null;

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
