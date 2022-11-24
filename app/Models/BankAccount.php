<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'companies_id',
        'banking_entity',
        'account_number',
        'cci',        
        'state',
        'description',
    ];
}
