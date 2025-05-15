<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Rules\ValidCityName;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::latest()->paginate(10);
        return view('admin.cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:15'],
            'province' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'name.required' => 'Nama Kota tidak boleh kosong.',
            'name.max' => 'Nama Kota tidak boleh lebih dari 15 karakter.',
            'province.max' => 'Province name must not exceed 50 characters.',
            'country.max' => 'Country name must not exceed 50 characters.',
            'latitude.numeric' => 'Latitude must be a number.',
            'longitude.numeric' => 'Longitude must be a number.',
        ]);

        City::create($data);
        return redirect()->route('admin.cities.index')->with('success', 'City added successfully.');
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
    public function edit(City $city)
    {
        return view('admin.cities.edit', compact('city'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'name' => 'required|string|max:15',
            'province' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'name.required' => 'Nama Kota tidak boleh kosong.',
            'name.max' => 'Nama Kota tidak boleh lebih dari 15 karakter.',
            'province.max' => 'Province name must not exceed 50 characters.',
            'country.max' => 'Country name must not exceed 50 characters.',
            'latitude.numeric' => 'Latitude must be a number.',
            'longitude.numeric' => 'Longitude must be a number.',
        ]);

        $city->update($data);
        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully.');
    }
}
