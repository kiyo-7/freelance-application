<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * List all payments for a project
     */
    public function index(Project $project)
    {
        $payments = Payment::where('project_id', $project->id)
            ->with(['payer', 'payee'])
            ->latest()
            ->get();

        return response()->json($payments);
    }

    /**
     * Store a new payment
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'payee_id' => 'required|exists:authusers,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,completed,failed',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        // Prevent user from paying themselves
        if ($data['payee_id'] == Auth::id()) {
            return response()->json(['message' => 'You cannot pay yourself.'], 400);
        }

        $payment = Payment::create([
            'project_id' => $project->id,
            'payer_id' => Auth::id(),
            'payee_id' => $data['payee_id'],
            'amount' => $data['amount'],
            'status' => $data['status'],
            'transaction_id' => $data['transaction_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment
        ], 201);
    }

    /**
     * Show a single payment
     */
    public function show(Payment $payment)
    {
        return $payment->load(['payer', 'payee', 'project']);
    }
}
