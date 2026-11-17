<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // FIX 1: bigger size limit (10 MB) and WebP allowed
        $request->validate([
            'food_name' => 'required|string|max:255',
            'consumed_at' => 'required|date',
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // FIX 2 + 3: model and key come from config (config/services.php), not env()
        $geminiKey = config('services.gemini.key');
        $geminiModel = config('services.gemini.model');

        // Default data if the AI call fails
        $calories = 0;
        $nutritionDetails = [
            'protein_g' => 0,
            'carbs_g' => 0,
            'fat_g' => 0,
            'ai_comment' => 'Gagal menganalisis makanan.',
        ];
        $aiFailed = true;

        $parts = [
            [
                'text' => "Analyze this food: '{$request->food_name}'. Provide an estimated total calorie count and macronutrients in grams. Return ONLY a JSON object with this exact structure: {\"calories\": integer, \"protein_g\": integer, \"carbs_g\": integer, \"fat_g\": integer, \"ai_comment\": \"A short 1-sentence health insight in Indonesian\"}"
            ]
        ];

        // Photo upload + base64 for Gemini
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoPath = $file->store('food_photos', 'public');

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $file->getMimeType(),
                    'data' => base64_encode(file_get_contents($file->getRealPath())),
                ]
            ];
        }

        // FIX 4: the API call can never crash the request anymore
        if ($geminiKey) {
            try {
                $response = Http::timeout(20)->retry(3, 2000, throw: false)
                    ->withHeaders(['x-goog-api-key' => $geminiKey])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent", [
                        'contents' => [
                            ['parts' => $parts]
                        ],
                        'generationConfig' => [
                            'response_mime_type' => 'application/json',
                        ],
                    ]);

                if ($response->successful()) {
                    $content = $response->json('candidates.0.content.parts.0.text');
                    $aiData = $content ? json_decode($content, true) : null;

                    if (is_array($aiData)) {
                        $calories = (int) ($aiData['calories'] ?? 0);
                        $nutritionDetails = [
                            'protein_g' => $aiData['protein_g'] ?? 0,
                            'carbs_g' => $aiData['carbs_g'] ?? 0,
                            'fat_g' => $aiData['fat_g'] ?? 0,
                            'ai_comment' => $aiData['ai_comment'] ?? 'Data nutrisi berhasil diproses.',
                        ];
                        $aiFailed = false;
                    } else {
                        Log::warning('Gemini returned content that is not valid JSON', ['content' => $content]);
                    }
                } else {
                    Log::warning('Gemini request failed', [
                        'model' => $geminiModel,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Gemini call threw an exception: ' . $e->getMessage());
            }
        } else {
            Log::warning('GEMINI_API_KEY is not set, skipping AI analysis.');
        }

        // Save to database
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

        $redirect = redirect()->route('food-logs.index');

        return $aiFailed
            ? $redirect->with('warning', 'Makanan tersimpan, tetapi analisis AI gagal sehingga kalori diset 0.')
            : $redirect->with('success', 'Makanan berhasil dicatat dan dianalisis oleh AI!');
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