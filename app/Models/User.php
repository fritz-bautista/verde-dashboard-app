<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // ✅ Added role
        'college_id',    // ✅ Optional: assign user to a college (for filtering)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
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
     * ✅ Role helper functions
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCollege(): bool
    {
        return $this->role === 'college';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // App\Models\User.php

    public function isUtility(): bool
    {
        return $this->role === 'utility';
    }


    /**
     * ✅ Relationships
     */
    public function wastePickups()
    {
        return $this->hasMany(WastePickup::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);

    }






}
