<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Aidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'apartment_id',
        'user_id',
        'month',
        'year',
        'amount',
        'late_fee',
        'total_amount',
        'due_date',
        'paid_date',
        'status',
        'notes',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_LATE = 'late';
    const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'datetime',
        ];
    }

    /**
     * Get the site this aidat belongs to.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the apartment this aidat belongs to.
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Get the user this aidat belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment for this aidat.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope to filter pending aidats.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter paid aidats.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope to filter late aidats.
     */
    public function scopeLate($query)
    {
        return $query->where('status', self::STATUS_LATE);
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeForPeriod($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Check if aidat is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->due_date < Carbon::now();
    }

    /**
     * Calculate late fee based on days overdue.
     */
    public function calculateLateFee(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $daysOverdue = Carbon::now()->diffInDays($this->due_date);
        $lateFeeRate = config('smartyonetim.late_fee_rate', 0.03); // 3% per month
        $monthsOverdue = ceil($daysOverdue / 30);
        
        return $this->amount * $lateFeeRate * $monthsOverdue;
    }

    /**
     * Mark aidat as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_date' => Carbon::now(),
        ]);
    }

    /**
     * Update late fee and total amount.
     */
    public function updateAmounts(): void
    {
        $lateFee = $this->calculateLateFee();
        $this->update([
            'late_fee' => $lateFee,
            'total_amount' => $this->amount + $lateFee,
            'status' => $this->isOverdue() ? self::STATUS_LATE : $this->status,
        ]);
    }

    /**
     * Get the period string (Month Year).
     */
    public function getPeriodAttribute(): string
    {
        $monthNames = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
        ];
        
        return $monthNames[$this->month] . ' ' . $this->year;
    }
}