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
        'start',
        'end',
        'middles',
        'oneup',
        'twoup',
        'threeup',
        'fourup',
        'fiveup',
        'sixup',
        'sevenup',
        'eightup',
        'nineup',
        'tenup',
        'onedown',
        'twodown',
        'threedown',
        'fourdown',
        'fivedown',
        'sixdown',
        'sevendown',
        'eightdown',
        'ninedown',
        'tendown',
        'upneighbours',
        'downneighbours',
        'usn',
    ];
}
