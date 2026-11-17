<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'score_date', 'total_score', 'nutrition_score', 'activity_score', 'calories_in', 'calories_burned', 'ai_insight'])]
class HealthScore extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}