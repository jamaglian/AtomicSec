<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttackType extends Model
{
    use HasFactory;

    protected $table = 'attacks_types';

    protected $fillable = [
        'name',
        'params',
    ];

    protected $casts = [
        'params' => 'array',
    ];

    public $timestamps = true;
}
