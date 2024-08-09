<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    public function inventries()
    {
        return $this->hasOne(Inventry::class);
    }

}
