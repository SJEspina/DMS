<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'deadline',
        'customer',
        'price',
        'product',
        'qty',
        'downpayment',
        'status',
    ];
}