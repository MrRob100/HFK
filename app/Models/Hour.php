<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol1',
        'symbol2',
        'start',
        'end',
        'usn',
    ];
}
