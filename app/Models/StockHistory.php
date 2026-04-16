<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $fillable = [
        'supply_id',
        'quantity',
        'price',
        'total',
        'notes',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}