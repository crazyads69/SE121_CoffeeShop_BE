<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loyal extends Model
{
    protected $table = 'loyal';

    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'spending_min',
        'type',
        'amount',
    ];
}
