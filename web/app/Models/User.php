<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the businesses owned by the user.
     */
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    /**
     * Get the primary active business for the authenticated user.
     */
    public function currentBusiness(): Business
    {
        return $this->businesses()->firstOrCreate(
            ['user_id' => $this->id],
            [
                'name' => ($this->name ?: 'My') . "'s Store",
                'slug' => \Illuminate\Support\Str::slug(($this->name ?: 'business') . '-' . $this->id),
                'industry' => 'ecommerce',
                'currency' => 'PKR',
            ]
        );
    }
}
