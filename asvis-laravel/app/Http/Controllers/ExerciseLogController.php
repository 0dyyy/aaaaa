<?php

namespace App\Http\Controllers;

use App\Models\ExerciseLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExerciseLogController extends Controller
{
    public function index()
    {
        $exerciseLogs = Auth::user()->exerciseLogs()
            ->with('location')
            ->orderBy('exercised_at', 'desc')
            ->get();
            
        $locations = Location::orderBy('name', 'asc')->get();

        return view('exercise-logs.index', compact('exerciseLogs', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'exercise_type' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'exercised_at' => 'required|date',
        ]);

        $location = Location::findOrFail($request->location_id);
        
        // 1. Calculate Calories Burned (Basic MET estimate based on weight)
        $weight = Auth::user()->weight_kg ?? 65; // Default to 65kg if not set
        $caloriePerMinute = ($weight * 3.5 * 6) / 200; // Moderate exercise formula
        $caloriesBurned = round($request->duration_minutes * $caloriePerMinute);

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

        Auth::user()->exerciseLogs()->create([
            'location_id' => $request->location_id,
            'exercise_type' => $request->exercise_type,
            'duration_minutes' => $request->duration_minutes,
            'calories_burned' => $caloriesBurned,
            'air_quality_during_exercise' => $aqiString,
            'exercised_at' => $request->exercised_at,
        ]);

        return redirect()->route('exercise-logs.index')->with('success', 'Aktivitas olahraga berhasil dicatat!');
    }

    public function destroy(ExerciseLog $exerciseLog)
    {
        if ($exerciseLog->user_id !== Auth::id()) {
            abort(403);
        }

        $exerciseLog->delete();

        return redirect()->route('exercise-logs.index')->with('success', 'Log olahraga berhasil dihapus.');
    }
}