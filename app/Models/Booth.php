<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'section',
        'type',
        'status',
        'pos_x',
        'pos_y',
    ];
}