<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoothSpace extends Model
{
    protected $table = 'booth_spaces';
    
    protected $fillable = [
        'name', 'description', 'size', 'dimension', 'fair_code', 
        'status', 'x', 'y', 'start_x', 'start_y', 'cols', 'rows', 
        'width', 'height', 'color_inHex'
    ];
    
    public function assignment()
    {
        return $this->hasOne(BoothAssignment::class, 'booth_space_id');
    }
}