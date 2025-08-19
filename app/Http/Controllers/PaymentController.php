<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Aidat;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Payment::with(['site', 'apartment', 'user', 'aidat']);
        
        // Filter based on user role
        if ($user->role === 'sakin') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'site_yoneticisi') {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('manager_user_id', $user->id);
            });
        }
        
        // Add filters
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->input('site_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }
        
        $payments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $aidatId = $request->get('aidat_id');
        $aidat = null;
        
        if ($aidatId) {
            $aidat = Aidat::with(['apartment', 'site'])->findOrFail($aidatId);
            $this->authorize('pay', $aidat);
        }
        
        return view('payments.create', compact('aidat'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aidat_id' => 'required|exists:aidats,id',
            'payment_method' => 'required|in:credit_card,bank_transfer,cash',
            'payment_gateway' => 'required|in:iyzico,stripe,manual',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $aidat = Aidat::findOrFail($validated['aidat_id']);
        $this->authorize('pay', $aidat);
        
        try {
            $payment = $this->paymentService->createPayment(
                $aidat,
                $validated['payment_method'],
                $validated['payment_gateway'],
                $validated['notes'] ?? null
            );
            
            // If it's a manual payment, mark as completed
            if ($validated['payment_gateway'] === 'manual') {
                $this->paymentService->processManualPayment($payment, Auth::user());
            }
            
            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Ödeme kaydı oluşturuldu.');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        
        $payment->load(['site', 'apartment', 'user', 'aidat']);
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Process the payment.
     */
    public function process(Payment $payment)
    {
        $this->authorize('update', $payment);
        
        try {
            if ($payment->payment_gateway === 'manual') {
                $this->paymentService->processManualPayment($payment, Auth::user());
            } else {
                // Process through payment gateway
                $result = $this->paymentService->processGatewayPayment($payment);
                
                if (!$result['success']) {
                    return back()->with('error', $result['message']);
                }
            }
            
            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Ödeme başarıyla işlendi.');
                
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show payment receipt.
     */
    public function receipt(Payment $payment)
    {
        $this->authorize('view', $payment);
        
        if (!$payment->isSuccessful()) {
            return back()->with('error', 'Sadece başarılı ödemeler için makbuz görüntülenebilir.');
        }
        
        $payment->load(['site', 'apartment', 'user', 'aidat']);
        
        return view('payments.receipt', compact('payment'));
    }
}