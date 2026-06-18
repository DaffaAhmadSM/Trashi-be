<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['trans_id', 'category_id', 'actual_weight'])]
class TransactionDetail extends Model
{
    protected $primaryKey = 'detail_id';

    protected function casts(): array
    {
        return [
            'actual_weight' => 'float',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trans_id', 'trans_id');
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'category_id', 'category_id');
    }

    public function paymentFees(): HasMany
    {
        return $this->hasMany(PaymentFee::class, 'transaction_detail_id', 'detail_id');
    }
}
