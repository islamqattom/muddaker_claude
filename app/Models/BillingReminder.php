<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_fee_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function monthlyFee(): BelongsTo
    {
        return $this->belongsTo(MonthlyFee::class);
    }
}
