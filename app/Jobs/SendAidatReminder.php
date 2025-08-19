<?php

namespace App\Jobs;

use App\Models\Aidat;
use App\Mail\AidatReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAidatReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Aidat $aidat,
        public bool $isOverdueReminder = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load relationships
            $this->aidat->load(['user', 'apartment', 'site']);
            
            // Skip if aidat is already paid
            if ($this->aidat->status === Aidat::STATUS_PAID) {
                \Log::info('Skipping reminder for paid aidat', [
                    'aidat_id' => $this->aidat->id,
                ]);
                return;
            }
            
            // Send email reminder
            if ($this->aidat->user->email) {
                Mail::to($this->aidat->user->email)->send(
                    new AidatReminderMail($this->aidat, $this->isOverdueReminder)
                );
            }
            
            // Send SMS reminder if phone number exists
            if ($this->aidat->user->phone) {
                $this->sendSmsReminder();
            }
            
            \Log::info('Aidat reminder sent', [
                'aidat_id' => $this->aidat->id,
                'user_id' => $this->aidat->user_id,
                'is_overdue' => $this->isOverdueReminder,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send aidat reminder', [
                'aidat_id' => $this->aidat->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Send SMS reminder.
     */
    private function sendSmsReminder(): void
    {
        $message = $this->isOverdueReminder 
            ? "Sayın {$this->aidat->user->name}, {$this->aidat->period} dönemi aidat borcunuz ({$this->aidat->total_amount} TL) gecikmiştir. Lütfen ödemenizi yapınız."
            : "Sayın {$this->aidat->user->name}, {$this->aidat->period} dönemi aidatınız ({$this->aidat->amount} TL) yaklaşmaktadır. Son ödeme tarihi: {$this->aidat->due_date->format('d.m.Y')}";
            
        // Here you would integrate with your SMS provider
        // For now, just log the message
        \Log::info('SMS reminder', [
            'phone' => $this->aidat->user->phone,
            'message' => $message,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Aidat reminder job failed', [
            'aidat_id' => $this->aidat->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}