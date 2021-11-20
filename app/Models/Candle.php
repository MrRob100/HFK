<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candle extends Model
{
    use HasFactory;

    protected $fillable = [
        'pair',
        'unix',
        'o',
        'h',
        'l',
        'c',
        'ave',
    ];
}
