<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['trans_id', 'transaction_detail_id', 'name', 'category', 'price', 'currency'])]
class PaymentFee extends Model
{
    protected $primaryKey = 'fee_id';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trans_id', 'trans_id');
    }

    public function transactionDetail(): BelongsTo
    {
        return $this->belongsTo(TransactionDetail::class, 'transaction_detail_id', 'detail_id');
    }
}
