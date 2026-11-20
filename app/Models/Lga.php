<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Lga extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }

    public function communities(): HasManyThrough
    {
        return $this->hasManyThrough(Community::class, Ward::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
