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
     * User roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_SITE_YONETICISI = 'site_yoneticisi';
    const ROLE_SAKIN = 'sakin';
    const ROLE_FIRMA_CALISANI = 'firma_calisani';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'site_id',
        'apartment_id',
        'is_active',
        'kvkk_consent',
        'kvkk_consent_date',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'kvkk_consent_date' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'kvkk_consent' => 'boolean',
        ];
    }

    /**
     * Get the site that the user belongs to.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the apartment that the user belongs to.
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Get the payments made by this user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the aidat records for this user.
     */
    public function aidats()
    {
        return $this->hasMany(Aidat::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a site manager.
     */
    public function isSiteManager(): bool
    {
        return $this->hasRole(self::ROLE_SITE_YONETICISI);
    }

    /**
     * Check if user is a resident.
     */
    public function isResident(): bool
    {
        return $this->hasRole(self::ROLE_SAKIN);
    }

    /**
     * Check if user is a company employee.
     */
    public function isCompanyEmployee(): bool
    {
        return $this->hasRole(self::ROLE_FIRMA_CALISANI);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Scope to filter users by role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}