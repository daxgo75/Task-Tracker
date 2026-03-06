<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'category_id',
        'created_by',
        'title',
        'description',
        'due_date',
        'deleted_at',
        'deleted_by',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'deleted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Custom soft-delete: only show non-deleted tasks by default
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeOnlyTrashed(Builder $query): Builder
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        return $query->where('title', $operator, "%{$search}%");
    }

    public function scopeUpcomingDue(Builder $query, int $days = 7): Builder
    {
        return $query
            ->whereNull('deleted_at')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now()->toDateString())
            ->where('due_date', '<=', now()->addDays($days)->toDateString())
            ->whereHas('category', fn ($q) => $q->where('name', '!=', 'Done'));
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}
