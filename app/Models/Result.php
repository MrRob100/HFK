<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'pair',
        'candle_type',
        'count_above',
        'count_below',
        'sd_above',
        'sd_below',
        'sd_ab',
    ];
}
