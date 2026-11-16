<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'location_id', 'exercise_type', 'duration_minutes', 'calories_burned', 'air_quality_during_exercise', 'exercised_at'])]
class ExerciseLog extends Model
{
    protected function casts(): array
    {
        return [
            'exercised_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}