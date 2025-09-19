<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// use Illuminate\Notifications\Notifiable;

class Meeting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'location',
        // 'assignee',
        'starts_at',
        'ends_at',
        'link',
        'user_id',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'location', 'starts_at', 'ends_at', 'link']);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_users');
    }
}
