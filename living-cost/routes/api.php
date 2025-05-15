<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Security\Idor\TransactionController;
use App\Http\Controllers\Security\Fingerprint\FingerprintController;

Route::post('/login', function (Request $request) {
    $user = App\Models\User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken('api-token')->plainTextToken;
});

Route::post('/fingerprint/login', [FingerprintController::class, 'login']);

Route::post('transaction/store-encrypt', [TransactionController::class, 'storeWithEncryptedAmount']);
Route::post('transaction/store-no-encrypt', [TransactionController::class, 'storeWithoutEncryptedAmount']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
