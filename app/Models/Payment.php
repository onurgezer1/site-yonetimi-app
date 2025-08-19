<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'apartment_id',
        'user_id',
        'aidat_id',
        'amount',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'gateway_response',
        'status',
        'paid_at',
        'notes',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';

    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CASH = 'cash';

    const GATEWAY_IYZICO = 'iyzico';
    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_MANUAL = 'manual';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the site this payment belongs to.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the apartment this payment belongs to.
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Get the user who made this payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the aidat this payment is for.
     */
    public function aidat()
    {
        return $this->belongsTo(Aidat::class);
    }

    /**
     * Scope to filter completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter payments by method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to filter payments by gateway.
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);

        // Mark associated aidat as paid
        if ($this->aidat) {
            $this->aidat->markAsPaid();
        }
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Get payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CREDIT_CARD => 'Kredi Kartı',
            self::METHOD_BANK_TRANSFER => 'Banka Havalesi',
            self::METHOD_CASH => 'Nakit',
            default => 'Bilinmeyen',
        };
    }

    /**
     * Get payment gateway display name.
     */
    public function getPaymentGatewayDisplayAttribute(): string
    {
        return match($this->payment_gateway) {
            self::GATEWAY_IYZICO => 'iyzico',
            self::GATEWAY_STRIPE => 'Stripe',
            self::GATEWAY_MANUAL => 'Manuel',
            default => 'Bilinmeyen',
        };
    }
}