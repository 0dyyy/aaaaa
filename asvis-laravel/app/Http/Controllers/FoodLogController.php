<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FoodLogController extends Controller
{
    public function index(Request $request)
    {
        $foodLogs = Auth::user()->foodLogs()
            ->with('photos')
            ->orderBy('consumed_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->success('Data log makanan berhasil diambil', $foodLogs);
        }

        return view('food-logs.index', compact('foodLogs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'food_name' => 'nullable|required_without:photo|string|max:255',
            'consumed_at' => 'required|date',
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photo' => 'nullable|required_without:food_name|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $geminiKey = config('services.gemini.key');
        $geminiModel = config('services.gemini.model', 'gemini-2.5-flash');

        $foodName = trim((string) $request->food_name);
        $calories = 0;
        $nutritionDetails = [
            'protein_g' => 0,
            'carbs_g' => 0,
            'fat_g' => 0,
            'ai_comment' => 'Gagal menganalisis makanan.',
        ];
        $aiFailed = true;

        $promptText = $foodName !== ''
            ? "Analyze this food: '{$foodName}'."
            : "Identify the food in this image and analyze its nutrition.";

        $parts = [
            [
                'text' => $promptText . ' Provide an estimated total calorie count and macronutrients in grams. Return ONLY a JSON object with this exact structure: {"food_name": "Short dish name in Indonesian", "calories": integer, "protein_g": integer, "carbs_g": integer, "fat_g": integer, "ai_comment": "A short 1-sentence health insight in Indonesian"}'
            ]
        ];

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
                        if ($foodName === '' && ! empty($aiData['food_name'])) {
                            $foodName = $aiData['food_name'];
                        }
                        $calories = (int) ($aiData['calories'] ?? 0);
                        $nutritionDetails = [
                            'protein_g' => (int) ($aiData['protein_g'] ?? 0),
                            'carbs_g' => (int) ($aiData['carbs_g'] ?? 0),
                            'fat_g' => (int) ($aiData['fat_g'] ?? 0),
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

        $foodLog = Auth::user()->foodLogs()->create([
            'food_name' => $foodName !== '' ? $foodName : 'Makanan Tercatat',
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

        $message = $aiFailed
            ? 'Makanan tersimpan, tetapi analisis AI gagal sehingga kalori diset 0.'
            : 'Makanan berhasil dicatat dan dianalisis oleh AI!';

        if ($request->wantsJson()) {
            return $this->success($message, $foodLog->load('photos'), 201);
        }

        return redirect()->route('food-logs.index')->with($aiFailed ? 'warning' : 'success', $message);
    }

    public function show(Request $request, FoodLog $foodLog)
    {
        if ($foodLog->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->wantsJson()) {
            return $this->success('Data log makanan ditemukan', $foodLog->load('photos'));
        }

        return redirect()->route('food-logs.index');
    }

    public function update(Request $request, FoodLog $foodLog)
    {
        if ($foodLog->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'food_name' => 'sometimes|required|string|max:255',
            'calories' => 'sometimes|nullable|integer|min:0',
            'consumed_at' => 'sometimes|required|date',
        ]);

        $foodLog->update($data);

        if ($request->wantsJson()) {
            return $this->success('Log makanan berhasil diperbarui.', $foodLog->fresh('photos'));
        }

        return redirect()->route('food-logs.index')->with('success', 'Log makanan berhasil diperbarui.');
    }

    public function destroy(Request $request, FoodLog $foodLog)
    {
        if ($foodLog->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($foodLog->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $foodLog->delete();

        if ($request->wantsJson()) {
            return $this->success('Log makanan berhasil dihapus.');
        }

        return redirect()->route('food-logs.index')->with('success', 'Log makanan berhasil dihapus.');
    }
}