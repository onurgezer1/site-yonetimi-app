<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'district',
        'postal_code',
        'tax_number',
        'manager_user_id',
        'total_apartments',
        'total_area',
        'common_area_ratio',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'total_area' => 'decimal:2',
            'common_area_ratio' => 'decimal:4',
        ];
    }

    /**
     * Get the manager of this site.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    /**
     * Get all users belonging to this site.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all apartments in this site.
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    /**
     * Get all aidat records for this site.
     */
    public function aidats()
    {
        return $this->hasMany(Aidat::class);
    }

    /**
     * Get all payments for this site.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all expenses for this site.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get site residents.
     */
    public function residents()
    {
        return $this->users()->where('role', User::ROLE_SAKIN);
    }

    /**
     * Scope to filter active sites.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the total monthly aidat for the site.
     */
    public function getTotalMonthlyAidatAttribute()
    {
        return $this->apartments()->sum('monthly_aidat');
    }

    /**
     * Get the occupancy rate of the site.
     */
    public function getOccupancyRateAttribute()
    {
        $occupiedApartments = $this->apartments()->whereHas('residents')->count();
        return $this->total_apartments > 0 ? ($occupiedApartments / $this->total_apartments) * 100 : 0;
    }
}