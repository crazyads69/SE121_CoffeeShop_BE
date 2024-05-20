<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankConfig extends Model
{
    protected $table = 'bank_config';
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'bank_number',
        'bank_account_name',
        'api_key',
    ];
}
