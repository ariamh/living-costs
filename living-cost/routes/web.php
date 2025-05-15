<?php

use App\Models\LivingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\ProfileController;
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
});

Route::get('/api/search', function (Request $request) {
    $city = $request->input('city');

    $query = LivingCost::with('city')
        ->whereHas('city', fn($q) => $q->where('name', 'like', "%$city%")) // Contoh query Eloquent yang benar
        // ->whereHas('city', fn($q) => $q->whereRaw("name LIKE '%$city%'")) // Contoh query raw SQL yang salah
        // ->whereHas('city', fn($q) => $q->whereRaw("name LIKE ?", ["%$city%"])) // Contoh query raw SQL yang benar
        // $query = DB::table('cities')->where('name', 'like', "%{$city}%")->get(); // Contoh query DB yang benar
        ->latest()
        ->first();

    return $query
        ? response()->json(['success' => true, 'data' => $query])
        : response()->json(['success' => false], 404);
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

    Route::get('admin/living-costs/map', [LivingCostController::class, 'map'])
        ->name('admin.living_costs.map');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
