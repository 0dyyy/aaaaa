<?php

namespace App\Models; // MUST be capitalized

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Don't forget to import this

#[Fillable(['name','address','latitude','longitude'])]
class Location extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function exerciseLogs(): HasMany
    {
        return $this->hasMany(ExerciseLog::class);
    }
}