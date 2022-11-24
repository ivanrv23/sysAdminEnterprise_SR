<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;
    protected $fillable = [
        'companies_id',
        'coins_id',
        'customers_id',
        'voucher_number',
        'exchange_rate',   
        'transporte',  
        'descuento',
        'total',
        'date',
        'description',
        'state',
    ];
}
