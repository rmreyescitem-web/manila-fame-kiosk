<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoothAssignment extends Model
{
    protected $table = 'booth_assignments';

    protected $fillable = [
        'booth_space_id',
        'tag',
        'company_name',
    ];

    /**
     * Get the booth space associated with this assignment.
     */
    public function boothSpace(): BelongsTo
    {
        return $this->belongsTo(BoothSpace::class, 'booth_space_id');
    }
}