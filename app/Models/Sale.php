<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['address', 'id_city', 'count'];

    protected function city()
    {
        return $this->belongsTo(City::class);
    }
}
