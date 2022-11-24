<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    use HasFactory;
    protected $fillable = [
        'companies_id',
        'purchases_id',
        'payment',
        'debt',
        'description',
        'state',
        'date',
    ];
}
