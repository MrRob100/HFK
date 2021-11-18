<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol1',
        'symbol2',
        'candle_type',
        'count_above',
        'count_below',
        'count_middle',
        'sd_above',
        'sd_below',
    ];
}
