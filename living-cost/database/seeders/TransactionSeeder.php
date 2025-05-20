<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // atau create user baru

        Transaction::create([
            'user_id' => $user->id,
            'amount' => 125000,
            'description' => 'Test transaction',
        ]);
    }
}
