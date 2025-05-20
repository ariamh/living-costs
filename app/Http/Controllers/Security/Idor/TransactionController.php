<?php

namespace App\Http\Controllers\Security\Idor;

use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function storeWithEncryptedAmount()
    {
        $transaction = Transaction::create([
            'user_id' => 1,
            'amount' => 1000,
            'description' => 'Test transaction with encryption',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created with encrypted amount successfully',
            'data' => $transaction
        ]);
    }

    public function storeWithoutEncryptedAmount()
    {
        $transaction = Transaction::create([
            'user_id' => 1,
            'amount' => 2000,
            'description' => 'Test transaction without encryption',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created without encrypted amount successfully',
            'data' => $transaction
        ]);
    }

    public function show($id) {
        $transaction = Transaction::whereUserId($id)->with('user')->first();

        if (!$transaction) {
            abort(404, 'Transaction not found');
        }

        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
}
