<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FoodLogController extends Controller
{
    public function index()
    {
        $foodLogs = Auth::user()->foodLogs()
            ->with('photos')
            ->orderBy('consumed_at', 'desc')
            ->get();

        return view('food-logs.index', compact('foodLogs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'food_name' => 'required|string|max:255',
            'consumed_at' => 'required|date',
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $geminiKey = env('GEMINI_API_KEY');
        
        // Default fallback data if API fails
        $calories = 0;
        $nutritionDetails = [
            'protein_g' => 0,
            'carbs_g' => 0,
            'fat_g' => 0,
            'ai_comment' => 'Gagal menganalisis makanan.',
        ];

        // Prepare prompt and Gemini payload
        $parts = [
            [
                'text' => "Analyze this food: '{$request->food_name}'. Provide an estimated total calorie count and macronutrients in grams. Return ONLY a JSON object with this exact structure: {\"calories\": integer, \"protein_g\": integer, \"carbs_g\": integer, \"fat_g\": integer, \"ai_comment\": \"A short 1-sentence health insight in Indonesian\"}"
            ]
        ];

        // Handle Photo Upload & Base64 encoding for Gemini
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoPath = $file->store('food_photos', 'public');
            
            // Add image to Gemini request
            $mimeType = $file->getMimeType();
            $base64Image = base64_encode(file_get_contents($file->getRealPath()));
            
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Image
                ]
            ];
        }

        // Call Gemini API
        if ($geminiKey) {
            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                'contents' => [
                    ['parts' => $parts]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json' // Forces Gemini to return pure JSON
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                if ($content) {
                    $aiData = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $calories = $aiData['calories'] ?? 0;
                        $nutritionDetails = [
                            'protein_g' => $aiData['protein_g'] ?? 0,
                            'carbs_g' => $aiData['carbs_g'] ?? 0,
                            'fat_g' => $aiData['fat_g'] ?? 0,
                            'ai_comment' => $aiData['ai_comment'] ?? 'Data nutrisi berhasil diproses.',
                        ];
                    }
                }
            }
        }

        // Save to Database
        $foodLog = Auth::user()->foodLogs()->create([
            'food_name' => $request->food_name,
            'calories' => $calories,
            'nutrition_details' => $nutritionDetails,
            'location_name' => $request->location_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'consumed_at' => $request->consumed_at,
        ]);

        if ($photoPath) {
            $foodLog->photos()->create([
                'photo_path' => $photoPath,
            ]);
        }

        return redirect()->route('food-logs.index')->with('success', 'Makanan berhasil dicatat dan dianalisis oleh AI!');
    }

    public function destroy(FoodLog $foodLog)
    {
        if ($foodLog->user_id !== Auth::id()) {
            abort(403);
        }

        $foodLog->delete();

        return redirect()->route('food-logs.index')->with('success', 'Log makanan berhasil dihapus.');
    }
}