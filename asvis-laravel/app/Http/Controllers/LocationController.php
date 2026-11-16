<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('name', 'asc')->get();
        // Assuming you have a view to manage locations, e.g., resources/views/locations/index.blade.php
        return view('locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $response = Http::withHeaders([
            'User-Agent' => 'ASVIS-Laravel-App/1.0', 
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $request->address,
            'format' => 'json',
            'limit' => 1,
        ]);

        $data = $response->json();

        if (empty($data)) {
            return back()->withErrors(['address' => 'Alamat tidak ditemukan oleh sistem pemetaan. Coba masukkan alamat yang lebih spesifik (misal: nama jalan, kota).'])->withInput();
        }

        // Extract latitude and longitude from the top result
        $latitude = $data[0]['lat'];
        $longitude = $data[0]['lon'];

        Location::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        return back()->with('success', 'Lokasi berhasil ditambahkan beserta koordinat petanya!');
    }
}