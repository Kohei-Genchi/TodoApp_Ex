<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'due_time',
        'status',
        'location', // INBOX, TODAY, TEMPLATE, SCHEDULED
        'user_id',
        'category_id',
        'recurrence_type', // none, daily, weekly, monthly
        'recurrence_end_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_time' => 'datetime',
        'recurrence_end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Check if the todo is recurring
    public function isRecurring(): bool
    {
        return $this->recurrence_type !== 'none';
    }
}
