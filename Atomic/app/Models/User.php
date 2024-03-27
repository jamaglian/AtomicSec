<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string, boolean>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'global_admin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string, boolean>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'global_admin' => 'boolean'
        ];
    }
    
    /**
     * Check if the user is a global admin.
     *
     * @return bool
     */
    public function isGlobalAdmin()
    {
        return $this->global_admin == 1;
    }
}
