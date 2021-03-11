<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ammount_from',
        'amount_to',
        'percentage',
        'tenure'
    ];

    public function investments()
    {
        return $this->hasMany(Investments::class);
    }
}
