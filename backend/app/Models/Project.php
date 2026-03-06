<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['created_by', 'name', 'description', 'status'];

    protected $casts = [
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('deleted_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeSearch($query, string $search)
    {
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        return $query->where('name', $operator, "%{$search}%");
    }
}
