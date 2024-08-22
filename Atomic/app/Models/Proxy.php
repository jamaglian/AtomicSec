<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    use HasFactory;

    protected $table = 'proxys'; // Nome da tabela

    protected $fillable = [
        'ip',
        'port',
        'type',
        'working',
        'working_waf',
        'tested_at',
    ];

    protected $casts = [
        'working' => 'boolean',
        'working_waf' => 'boolean',
        'tested_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true; // Indica que o Laravel deve gerenciar os timestamps (created_at, updated_at)
}
