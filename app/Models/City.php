<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected function sales()
    {
        return $this->hasMany(Sale::class, 'id_city');
    }

    public function addSale(Sale $sale)
    {
        $this->sales()->save($sale);
    }
}
