<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'client_id',
        'level',
        'name',
        'hourly_rate',
        'status',
    ];

    protected $casts = [
        'hourly_rate' => 'integer',
        'status' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function employeeSchedules()
    {
        return $this->hasMany(EmployeeSchedule::class, 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
