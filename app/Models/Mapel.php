<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    protected $guarded = [];

    public function ujians(): HasMany
    {
        return $this->hasMany(Ujian::class);
    }
}