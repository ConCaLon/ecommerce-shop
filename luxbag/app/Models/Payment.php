<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'method', 'amount', 'status', 'transaction_id', 'gateway_response', 'paid_at'];
    protected $casts = ['gateway_response' => 'array', 'paid_at' => 'datetime', 'amount' => 'decimal:2'];

    public function order() { return $this->belongsTo(Order::class); }
}
