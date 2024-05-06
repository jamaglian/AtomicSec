<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applications extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'url',
        'type'
    ];
    /**
     * Get the companies associated with the application.
     */
    public function company()
    {
        return $this->hasOne(Companies::class, 'id', 'company_id');
    }
    /**
     * Get the analysis associated with the application.
     */
    public function analysis()
    {
        return $this->hasMany(ApplicationsAnalysis::class, 'application_id');
    }
}
