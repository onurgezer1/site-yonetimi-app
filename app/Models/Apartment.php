<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'apartment_number',
        'floor',
        'block',
        'area',
        'rooms',
        'ownership_share',
        'monthly_aidat',
        'is_occupied',
        'owner_name',
        'owner_phone',
        'owner_email',
    ];

    protected function casts(): array
    {
        return [
            'is_occupied' => 'boolean',
            'area' => 'decimal:2',
            'ownership_share' => 'decimal:4',
            'monthly_aidat' => 'decimal:2',
        ];
    }

    /**
     * Get the site that this apartment belongs to.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the residents living in this apartment.
     */
    public function residents()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all aidat records for this apartment.
     */
    public function aidats()
    {
        return $this->hasMany(Aidat::class);
    }

    /**
     * Get all payments for this apartment.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope to filter occupied apartments.
     */
    public function scopeOccupied($query)
    {
        return $query->where('is_occupied', true);
    }

    /**
     * Scope to filter vacant apartments.
     */
    public function scopeVacant($query)
    {
        return $query->where('is_occupied', false);
    }

    /**
     * Get the full apartment identifier (Block-Floor-Number).
     */
    public function getFullIdentifierAttribute()
    {
        $parts = array_filter([$this->block, $this->floor, $this->apartment_number]);
        return implode('-', $parts);
    }

    /**
     * Get the primary resident of this apartment.
     */
    public function getPrimaryResidentAttribute()
    {
        return $this->residents()->first();
    }

    /**
     * Calculate monthly aidat based on ownership share and total site expenses.
     */
    public function calculateMonthlyAidat($totalSiteExpenses = null)
    {
        if ($totalSiteExpenses === null) {
            return $this->monthly_aidat;
        }

        // Aidat calculation based on ownership share
        return ($totalSiteExpenses * $this->ownership_share / 100);
    }
}