<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Aidat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create a new payment record.
     */
    public function createPayment(
        Aidat $aidat, 
        string $paymentMethod,
        string $paymentGateway,
        string $notes = null
    ): Payment {
        return DB::transaction(function () use ($aidat, $paymentMethod, $paymentGateway, $notes) {
            
            // Check if aidat is already paid
            if ($aidat->status === Aidat::STATUS_PAID) {
                throw new \Exception('Bu aidat zaten ödenmiştir.');
            }
            
            // Update late fees
            $aidat->updateAmounts();
            
            $payment = Payment::create([
                'site_id' => $aidat->site_id,
                'apartment_id' => $aidat->apartment_id,
                'user_id' => $aidat->user_id,
                'aidat_id' => $aidat->id,
                'amount' => $aidat->total_amount,
                'payment_method' => $paymentMethod,
                'payment_gateway' => $paymentGateway,
                'transaction_id' => $this->generateTransactionId(),
                'status' => Payment::STATUS_PENDING,
                'notes' => $notes,
            ]);
            
            return $payment;
        });
    }

    /**
     * Process manual payment.
     */
    public function processManualPayment(Payment $payment, User $processedBy): Payment
    {
        return DB::transaction(function () use ($payment, $processedBy) {
            $payment->markAsCompleted([
                'processed_by' => $processedBy->id,
                'processed_at' => now(),
                'method' => 'manual',
            ]);
            
            return $payment;
        });
    }

    /**
     * Process payment through gateway.
     */
    public function processGatewayPayment(Payment $payment): array
    {
        try {
            switch ($payment->payment_gateway) {
                case Payment::GATEWAY_IYZICO:
                    return $this->processIyzicoPayment($payment);
                case Payment::GATEWAY_STRIPE:
                    return $this->processStripePayment($payment);
                default:
                    throw new \Exception('Desteklenmeyen ödeme gateway\'i');
            }
        } catch (\Exception $e) {
            $payment->markAsFailed([
                'error_message' => $e->getMessage(),
                'error_time' => now(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process iyzico payment.
     */
    private function processIyzicoPayment(Payment $payment): array
    {
        // This would integrate with iyzico API
        // For now, simulating a successful payment
        
        $gatewayResponse = [
            'gateway' => 'iyzico',
            'transaction_id' => 'IYZ_' . Str::random(16),
            'status' => 'success',
            'response_time' => now(),
        ];
        
        $payment->markAsCompleted($gatewayResponse);
        
        return [
            'success' => true,
            'message' => 'Ödeme başarıyla tamamlandı.',
            'transaction_id' => $gatewayResponse['transaction_id'],
        ];
    }

    /**
     * Process Stripe payment.
     */
    private function processStripePayment(Payment $payment): array
    {
        // This would integrate with Stripe API
        // For now, simulating a successful payment
        
        $gatewayResponse = [
            'gateway' => 'stripe',
            'payment_intent_id' => 'pi_' . Str::random(24),
            'status' => 'succeeded',
            'response_time' => now(),
        ];
        
        $payment->markAsCompleted($gatewayResponse);
        
        return [
            'success' => true,
            'message' => 'Ödeme başarıyla tamamlandı.',
            'payment_intent_id' => $gatewayResponse['payment_intent_id'],
        ];
    }

    /**
     * Get payment statistics for a site.
     */
    public function getPaymentStatistics($site, int $year = null): array
    {
        $year = $year ?? now()->year;
        
        $baseQuery = $site->payments()->whereYear('created_at', $year);
        
        return [
            'total_payments' => $baseQuery->count(),
            'completed_payments' => $baseQuery->completed()->count(),
            'pending_payments' => $baseQuery->pending()->count(),
            'failed_payments' => $baseQuery->failed()->count(),
            'total_amount' => $baseQuery->sum('amount'),
            'completed_amount' => $baseQuery->completed()->sum('amount'),
            'pending_amount' => $baseQuery->pending()->sum('amount'),
            'success_rate' => $this->calculateSuccessRate($site, $year),
        ];
    }

    /**
     * Calculate payment success rate.
     */
    private function calculateSuccessRate($site, int $year): float
    {
        $totalPayments = $site->payments()->whereYear('created_at', $year)->count();
        $successfulPayments = $site->payments()
            ->completed()
            ->whereYear('paid_at', $year)
            ->count();
            
        return $totalPayments > 0 ? ($successfulPayments / $totalPayments) * 100 : 0;
    }

    /**
     * Get monthly payment summary.
     */
    public function getMonthlyPaymentSummary($site, int $year): array
    {
        $summary = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $site->payments()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->selectRaw('
                    COUNT(*) as total_count,
                    COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_count,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as completed_amount
                ')
                ->first();
                
            $summary[$month] = [
                'month' => $month,
                'total_payments' => $monthData->total_count ?? 0,
                'completed_payments' => $monthData->completed_count ?? 0,
                'pending_payments' => $monthData->pending_count ?? 0,
                'failed_payments' => $monthData->failed_count ?? 0,
                'total_amount' => $monthData->total_amount ?? 0,
                'completed_amount' => $monthData->completed_amount ?? 0,
                'success_rate' => $monthData->total_count > 0 
                    ? ($monthData->completed_count / $monthData->total_count) * 100 
                    : 0,
            ];
        }
        
        return $summary;
    }

    /**
     * Generate unique transaction ID.
     */
    private function generateTransactionId(): string
    {
        return 'TXN_' . date('Ymd') . '_' . Str::random(8);
    }

    /**
     * Refund a payment.
     */
    public function refundPayment(Payment $payment, string $reason, User $refundedBy): bool
    {
        if (!$payment->isSuccessful()) {
            throw new \Exception('Sadece başarılı ödemeler iade edilebilir.');
        }

        return DB::transaction(function () use ($payment, $reason, $refundedBy) {
            // Process refund through gateway if needed
            $refundResponse = $this->processGatewayRefund($payment);
            
            if (!$refundResponse['success']) {
                throw new \Exception($refundResponse['message']);
            }
            
            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'refund' => [
                            'refunded_at' => now(),
                            'refunded_by' => $refundedBy->id,
                            'reason' => $reason,
                            'refund_response' => $refundResponse,
                        ]
                    ]
                )
            ]);
            
            // Update aidat status back to pending
            if ($payment->aidat) {
                $payment->aidat->update(['status' => Aidat::STATUS_PENDING]);
            }
            
            return true;
        });
    }

    /**
     * Process refund through gateway.
     */
    private function processGatewayRefund(Payment $payment): array
    {
        // This would process actual refund through payment gateway
        // For now, simulating successful refund
        
        return [
            'success' => true,
            'refund_id' => 'RFD_' . Str::random(12),
            'message' => 'İade işlemi başarıyla tamamlandı.',
        ];
    }
}