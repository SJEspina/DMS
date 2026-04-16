<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyTransaction extends Model
{
    protected $fillable = [
        'supply_id',
        'type',
        'quantity',
        'unit_price',
        'total_cost',
        'notes',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}