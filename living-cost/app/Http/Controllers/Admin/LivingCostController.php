<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\LivingCost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LivingCostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-living-cost');

        $costs = LivingCost::with('city')->latest()->paginate(10);
        return view('admin.living_costs.index', compact('costs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = City::all();
        return view('admin.living_costs.create', compact('cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'housing' => 'nullable|numeric',
            'food' => 'nullable|numeric',
            'transportation' => 'nullable|numeric',
            'utilities' => 'nullable|numeric',
            'healthcare' => 'nullable|numeric',
            'entertainment' => 'nullable|numeric',
            'other' => 'nullable|numeric',
        ]);

        $total = collect($validated)->only([
            'housing', 'food', 'transportation', 'utilities', 'healthcare', 'entertainment', 'other'
        ])->sum();

        LivingCost::create(array_merge($validated, [
            'user_id' => Auth::guard('web')->id(),
            'total_estimation' => $total
        ]));

        return redirect()->route('admin.living_costs.index')->with('success', 'Data created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LivingCost $livingCost)
    {
        $cities = City::all();
        return view('admin.living_costs.edit', compact('livingCost', 'cities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LivingCost $livingCost)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'housing' => 'nullable|numeric',
            'food' => 'nullable|numeric',
            'transportation' => 'nullable|numeric',
            'utilities' => 'nullable|numeric',
            'healthcare' => 'nullable|numeric',
            'entertainment' => 'nullable|numeric',
            'other' => 'nullable|numeric',
        ]);

        $total = collect($validated)->only([
            'housing', 'food', 'transportation', 'utilities', 'healthcare', 'entertainment', 'other'
        ])->sum();

        $livingCost->update(array_merge($validated, [
            'total_estimation' => $total
        ]));

        return redirect()->route('admin.living_costs.index')->with('success', 'Data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LivingCost $livingCost)
    {
        $livingCost->delete();
        return redirect()->back()->with('success', 'Data deleted successfully');
    }

    public function map()
    {
        $cities = City::all();
        return view('admin.living_costs.map', compact('cities'));
    }
}
