<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['food_log_id', 'photo_path'])]
class FoodPhoto extends Model
{
    /**
     * Get the food log that owns this photo.
     */
    public function foodLog(): BelongsTo
    {
        return $this->belongsTo(FoodLog::class);
    }
}