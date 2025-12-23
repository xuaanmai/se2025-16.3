<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Epic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'project_id', 'starts_at', 'ends_at',
        'parent_id'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'epic_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Epic::class, 'parent_id', 'id');
    }

    public function sprint(): HasOne
    {
        return $this->hasOne(Sprint::class, 'epic_id', 'id');
    }

    /**
     * Recalculate the progress of the epic based on its tickets.
     */
    public function recalculateProgress()
    {
        $stats = $this->tickets()
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when exists (select * from `ticket_statuses` where `tickets`.`status_id` = `ticket_statuses`.`id` and `is_final` = 1) then 1 end) as closed")
            ->first();

        $this->progress = $stats->total > 0 ? round(($stats->closed / $stats->total) * 100) : 0;
        $this->save();
    }

    /**
     * Recalculate the start and end dates of the epic based on its tickets.
     */
    public function recalculateDates()
    {
        $minDate = $this->tickets()->min('start_date');
        $maxDate = $this->tickets()->max('due_date');

        if ($minDate && $maxDate) {
            $this->starts_at = $minDate;
            $this->ends_at = $maxDate;
            $this->save();
        }
    }
}
