<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'address_id', 'date', 'total_paid'])]
class Transaction extends Model
{
    protected $primaryKey = 'trans_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'total_paid' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'trans_id', 'trans_id');
    }

    public function paymentFees(): HasMany
    {
        return $this->hasMany(PaymentFee::class, 'trans_id', 'trans_id');
    }
}
