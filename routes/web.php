<?php

use App\Models\LivingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\LivingCostController;
use App\Http\Controllers\Security\Idor\TransactionController;
use App\Http\Controllers\Security\DatabaseTransaction\DatabaseTransactionController;

Route::middleware('auth')->get('transaction/{id}', [TransactionController::class, 'show']);

Route::middleware('auth')->get('transaction/demo', [DatabaseTransactionController::class, 'demo']);
// Rawan race condition (tidak ada lock)
Route::middleware('auth')->get('/transaction/no-lock/{id}/{amount}', [DatabaseTransactionController::class, 'addAmountNoLock']);
// Aman, pakai transaction dan row lock
Route::middleware('auth')->get('/transaction/with-lock/{id}/{amount}', [DatabaseTransactionController::class, 'addAmountWithLock']);
// Simulasi rollback
Route::middleware('auth')->get('/transaction/rollback/{id}/{amount}', [DatabaseTransactionController::class, 'demoTransactionRollback']);

Route::get('/', function () {
    $livingCosts = LivingCost::with('city')->latest()->get();
    return view('welcome', compact('livingCosts'));
})->name('home');

Route::get('/api/search', function (Request $request) {
    $cityName = $request->query('city');

    $livingCost = LivingCost::with('city')
        ->whereHas('city', fn($q) => $q->where('name', 'like', '%' . $cityName . '%'))
        ->first();

    if (!$livingCost) {
        return response()->json(['success' => false, 'message' => 'City not found.']);
    }

    $prompt = "Tuliskan ringkasan biaya hidup di kota berikut ini dalam bahasa Indonesia, maksimal 2 kalimat. " .
        "Kalimat pertama menjelaskan komponen utama pengeluaran secara ringkas. " .
        "Kalimat kedua berikan kesimpulan atau saran berdasarkan data tersebut, misalnya apakah kota ini cocok untuk keluarga, mahasiswa, atau pekerja profesional.\n\n" .
        "Nama Kota: {$livingCost->city->name}\n" .
        "Perumahan: Rp{$livingCost->housing}\n" .
        "Makanan: Rp{$livingCost->food}\n" .
        "Transportasi: Rp{$livingCost->transportation}\n" .
        "Utilitas: Rp{$livingCost->utilities}\n" .
        "Kesehatan: Rp{$livingCost->healthcare}\n" .
        "Hiburan: Rp{$livingCost->entertainment}\n" .
        "Lain-lain: Rp{$livingCost->other}\n" .
        "Total Estimasi: Rp{$livingCost->total_estimation}\n";

    $isLocal = app()->environment('local');
    $analysis = 'AI analysis unavailable.';

    try {
        $response = Http::withOptions([
            'verify' => !$isLocal ? true : false,
        ])->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . env('GEMINI_API_KEY'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $analysis = $response->json('candidates.0.content.parts.0.text') ?? 'No insight returned.';
        }
    } catch (\Exception $e) {
        $analysis = 'AI error: ' . $e->getMessage();
    }

    return response()->json([
        'success' => true,
        'data' => $livingCost,
        'analysis' => $analysis,
    ]);
});

Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('cities', CityController::class)->names([
        'index' => 'admin.cities.index',
        'create' => 'admin.cities.create',
        'store' => 'admin.cities.store',
        'edit' => 'admin.cities.edit',
        'update' => 'admin.cities.update',
        'destroy' => 'admin.cities.destroy'
    ]);

    Route::resource('living-costs', LivingCostController::class)->names([
        'index' => 'admin.living_costs.index',
        'create' => 'admin.living_costs.create',
        'store' => 'admin.living_costs.store',
        'edit' => 'admin.living_costs.edit',
        'update' => 'admin.living_costs.update',
        'destroy' => 'admin.living_costs.destroy'
    ]);

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/download/{document}', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{filename}', [DocumentController::class, 'show'])->name('documents.show');

    Route::get('admin/living-costs/map', [LivingCostController::class, 'map'])
        ->name('admin.living_costs.map');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
