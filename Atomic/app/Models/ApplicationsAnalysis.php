<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationsAnalysis extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'applications_analysis';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'analysis',
        'pid',
        'log',
        'created_at',
        'updated_at'
    ];
    /**
     * Get the application associated with the analysis.
     */
    public function application()
    {
        return $this->hasOne(Applications::class, 'id', 'application_id');
    }
}
