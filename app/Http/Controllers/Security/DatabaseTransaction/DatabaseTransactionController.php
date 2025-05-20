<?php

namespace App\Http\Controllers\Security\DatabaseTransaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DatabaseTransactionController extends Controller
{

    public function demo()
    {
        return view('transaction-demo');
    }

    // Tanpa transaction & lock (rawan race condition)
    public function addAmountNoLock($transactionId, $amount)
    {
        $expense = Transaction::find($transactionId);
        $expense->amount += $amount;
        sleep(2); // Simulasi delay
        $expense->save();
        return response()->json(['amount' => $expense->amount]);
    }

    // Dengan transaction & row lock (aman)
    public function addAmountWithLock($transactionId, $amount)
    {
        DB::transaction(function () use ($transactionId, $amount) {
            $expense = Transaction::where('id', $transactionId)->lockForUpdate()->first();
            $expense->amount += $amount;
            sleep(2); // Simulasi delay
            $expense->save();
        });
        $expense = Transaction::find($transactionId);
        return response()->json(['amount' => $expense->amount]);
    }

    // Demo rollback: update amount lalu force error agar rollback
    public function demoTransactionRollback($transactionId, $amount)
    {
        try {
            DB::transaction(function () use ($transactionId, $amount) {
                $expense = Transaction::where('id', $transactionId)->lockForUpdate()->first();
                $expense->amount += $amount;
                $expense->save();
                // Simulasi error
                throw new \Exception('Simulasi error, transaksi di-rollback!');
            });
        } catch (\Exception $e) {
            $expense = Transaction::find($transactionId);
            return response()->json([
                'amount' => $expense->amount,
                'error' => $e->getMessage(),
                'info' => 'Perubahan amount dibatalkan karena error.'
            ], 400);
        }
    }
}
