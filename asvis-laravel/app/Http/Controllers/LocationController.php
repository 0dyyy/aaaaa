<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::orderBy('name', 'asc')->get();

        if ($request->wantsJson()) {
            return $this->success('Data lokasi berhasil diambil', $locations);
        }

        // Assuming you have a view to manage locations, e.g., resources/views/locations/index.blade.php
        return view('locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // Alamat opsional, tapi wajib kalau peta tidak diklik
            'address' => 'nullable|required_without:latitude|string|max:255',
            // Koordinat dari klik peta (opsional kalau alamat diisi)
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        // Kalau peta tidak diklik, cari koordinat dari alamat (OpenStreetMap)
        if (! $latitude || ! $longitude) {
            $response = Http::withHeaders([
                'User-Agent' => 'ASVIS-Laravel-App/1.0',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->address,
                'format' => 'json',
                'limit' => 1,
            ]);

            $data = $response->json();

            if (empty($data)) {
                $message = 'Alamat tidak ditemukan oleh sistem pemetaan. Coba masukkan alamat yang lebih spesifik atau klik lokasi di peta.';

                if ($request->wantsJson()) {
                    return $this->error($message, 422);
                }

                return back()->withErrors(['address' => $message])->withInput();
            }

            $latitude = $data[0]['lat'];
            $longitude = $data[0]['lon'];
        }

        $location = Location::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        if ($request->wantsJson()) {
            return $this->success('Lokasi berhasil ditambahkan beserta koordinat petanya!', $location, 201);
        }

        return back()->with('success', 'Lokasi berhasil ditambahkan beserta koordinat petanya!');
    }
}