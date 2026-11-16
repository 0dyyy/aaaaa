<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'food_name', 'calories', 'nutrition_details', 'location_name', 'latitude', 'longitude', 'consumed_at'])]
class FoodLog extends Model
{
    protected function casts(): array
    {
        return [
            'consumed_at' => 'datetime',
            'nutrition_details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FoodPhoto::class);
    }
}