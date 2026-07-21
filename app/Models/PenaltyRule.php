<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyRule extends Model
{
    protected $fillable = [
        'client_id',
        'absence_count',
        'absence_rate',
        'late_minutes',
        'late_rate',
    ];

    protected $casts = [
        'absence_count' => 'integer',
        'absence_rate' => 'decimal:2',
        'late_minutes' => 'integer',
        'late_rate' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public static function defaults(): array
    {
        return [
            'absence_count' => 1,
            'absence_rate' => 7.00,
            'late_minutes' => 30,
            'late_rate' => 5.00,
        ];
    }

    public static function forClientOrDefaults(int $clientId): self
    {
        return static::where('client_id', $clientId)->first()
            ?? new static(array_merge(['client_id' => $clientId], static::defaults()));
    }
}
