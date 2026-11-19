<?php

namespace App\Http\Controllers;

use App\Models\ExerciseLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExerciseLogController extends Controller
{
    public function index(Request $request)
    {
        $exerciseLogs = Auth::user()->exerciseLogs()
            ->with('location')
            ->orderBy('exercised_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->success('Data log olahraga berhasil diambil', $exerciseLogs);
        }

        $locations = Location::orderBy('name', 'asc')->get();

        return view('exercise-logs.index', compact('exerciseLogs', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Pilih lokasi tersimpan ATAU klik titik bebas di peta (latitude + longitude)
            'location_id' => 'nullable|required_without:latitude|exists:locations,id',
            'latitude' => 'nullable|required_without:location_id|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'exercise_type' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'exercised_at' => 'required|date',
        ]);

        $location = $request->filled('location_id')
            ? Location::findOrFail($request->location_id)
            : $this->findOrCreateLocation($request);

        // 1. Calculate Calories Burned (Basic MET estimate based on weight)
        $caloriesBurned = $this->caloriesBurned($request->duration_minutes);

        // 2. Fetch Air Quality Index from WAQI
        $waqiToken = env('WAQI_API_KEY');
        $aqiString = "Data tidak tersedia";
        
        if ($waqiToken && $location->latitude && $location->longitude) {
            $response = Http::timeout(5)->get("https://api.waqi.info/feed/geo:{$location->latitude};{$location->longitude}/", [
                'token' => $waqiToken
            ]);

            if ($response->successful() && $response->json('status') === 'ok') {
                $aqiValue = $response->json('data.aqi');
                
                // Categorize AQI
                if ($aqiValue <= 50) {
                    $category = 'Baik';
                } elseif ($aqiValue <= 100) {
                    $category = 'Sedang';
                } elseif ($aqiValue <= 150) {
                    $category = 'Tidak Sehat bagi Kelompok Sensitif';
                } elseif ($aqiValue <= 200) {
                    $category = 'Tidak Sehat';
                } else {
                    $category = 'Sangat Tidak Sehat / Berbahaya';
                }

                $aqiString = "{$category} (AQI: {$aqiValue})";
            }
        }

        $exerciseLog = Auth::user()->exerciseLogs()->create([
            'location_id' => $location->id,
            'exercise_type' => $request->exercise_type,
            'duration_minutes' => $request->duration_minutes,
            'calories_burned' => $caloriesBurned,
            'air_quality_during_exercise' => $aqiString,
            'exercised_at' => $request->exercised_at,
        ]);

        if ($request->wantsJson()) {
            return $this->success('Aktivitas olahraga berhasil dicatat!', $exerciseLog->load('location'), 201);
        }

        return redirect()->route('exercise-logs.index')->with('success', 'Aktivitas olahraga berhasil dicatat!');
    }

    public function show(Request $request, ExerciseLog $exerciseLog)
    {
        if ($exerciseLog->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->wantsJson()) {
            return $this->success('Data log olahraga ditemukan', $exerciseLog->load('location'));
        }

        return redirect()->route('exercise-logs.index');
    }

    public function update(Request $request, ExerciseLog $exerciseLog)
    {
        if ($exerciseLog->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'location_id' => 'sometimes|required|exists:locations,id',
            'exercise_type' => 'sometimes|required|string|max:255',
            'duration_minutes' => 'sometimes|required|integer|min:1',
            'exercised_at' => 'sometimes|required|date',
        ]);

        // Hitung ulang kalori kalau durasi berubah
        if (isset($data['duration_minutes'])) {
            $data['calories_burned'] = $this->caloriesBurned($data['duration_minutes']);
        }

        $exerciseLog->update($data);

        if ($request->wantsJson()) {
            return $this->success('Log olahraga berhasil diperbarui.', $exerciseLog->fresh('location'));
        }

        return redirect()->route('exercise-logs.index')->with('success', 'Log olahraga berhasil diperbarui.');
    }

    public function destroy(Request $request, ExerciseLog $exerciseLog)
    {
        if ($exerciseLog->user_id !== Auth::id()) {
            abort(403);
        }

        $exerciseLog->delete();

        if ($request->wantsJson()) {
            return $this->success('Log olahraga berhasil dihapus.');
        }

        return redirect()->route('exercise-logs.index')->with('success', 'Log olahraga berhasil dihapus.');
    }

    // Kalori terbakar = durasi x (berat x 3,5 x 6) / 200, berat default 65 kg
    private function caloriesBurned(int $minutes): int
    {
        $weight = Auth::user()->weight_kg ?? 65; // Default to 65kg if not set
        $caloriePerMinute = ($weight * 3.5 * 6) / 200; // Moderate exercise formula

        return (int) round($minutes * $caloriePerMinute);
    }

    // Titik bebas dari peta: pakai lokasi tersimpan dalam radius ±100 m, kalau tidak ada buat lokasi baru
    private function findOrCreateLocation(Request $request): Location
    {
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $delta = 0.001; // ±0,001 derajat ≈ 110 m

        $nearest = Location::whereBetween('latitude', [$lat - $delta, $lat + $delta])
            ->whereBetween('longitude', [$lng - $delta, $lng + $delta])
            ->get()
            ->sortBy(fn ($loc) => ($loc->latitude - $lat) ** 2 + ($loc->longitude - $lng) ** 2)
            ->first();

        if ($nearest) {
            return $nearest;
        }

        return Location::create([
            'name' => $request->filled('location_name')
                ? $request->location_name
                : 'Titik ' . round($lat, 5) . ', ' . round($lng, 5),
            'address' => null,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }
}