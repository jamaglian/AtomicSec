<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationAttack extends Model
{
    use HasFactory;
    protected $table = 'application_attacks';

    protected $fillable = [
        'application_id',
        'attacks_types_id',
        'attack_params',
        'attack_analysis',
        'log',
        'status',
        'started_at',
        'finish_at',
    ];

    protected $casts = [
        'attack_analysis' => 'array',
        'started_at' => 'datetime',
        'finish_at' => 'datetime',
    ];

    public $timestamps = true;

    // Define relationships
    public function application()
    {
        return $this->belongsTo(Applications::class);
    }

    public function attackType()
    {
        return $this->belongsTo(AttackType::class, 'attacks_types_id');
    }
}
