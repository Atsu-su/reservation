<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    public function lendingAggregate()
    {
        return $this->hasMany(LendingAggregate::class);
    }

}
