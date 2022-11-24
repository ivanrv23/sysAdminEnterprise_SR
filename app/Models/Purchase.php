<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'companies_id',
        'providers_id',
        'payment_methods_id',
        'proof_payments_id',
        'coins_id',
        'exchange_rate',
        'total',
        'date',
        'state',
        'description',
    ];
}
