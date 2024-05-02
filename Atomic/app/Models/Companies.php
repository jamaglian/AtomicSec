<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Companies extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_owner_id',
        'name'
    ];

    /**
     * Get the applications associated with the company.
     */
    public function applications()
    {
        return $this->hasMany(Applications::class, 'company_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'company_owner_id');
    }
}
