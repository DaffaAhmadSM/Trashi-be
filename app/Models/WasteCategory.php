<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name_category'])]
class WasteCategory extends Model
{
    protected $primaryKey = 'category_id';

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'category_id', 'category_id');
    }
}
