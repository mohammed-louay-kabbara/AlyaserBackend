<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name', 'phone', 'password','user_number','force_password_change', 'zone', 'shop_name', 'address', 'role', 'role_id', 'activated', 'fcm_token'
    ];

    use Notifiable;
    
    // Rest omitted for brevity
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get the role that belongs to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the permissions through the user's role.
     */
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            'role_permission',
            'role_id',
            'id',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if user has any of the specified permissions.
     */
    public function hasAnyPermission($permissions)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()
            ->whereIn('name', (array) $permissions)
            ->exists();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->role && $this->role->name_en === $role;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}