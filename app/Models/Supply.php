<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'status',
    ];

    public function transactions()
    {
        return $this->hasMany(SupplyTransaction::class);
    }
}