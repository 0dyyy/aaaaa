<?php

namespace App\Http\Controllers;

use App\Models\HealthScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthScoreController extends Controller
{
    private const DEFAULT_CALORIE_TARGET = 2000;

    public function index(Request $request)
    {
        $user = Auth::user();

        // Postman / API: kembalikan riwayat skor dalam JSON
        if ($request->wantsJson()) {
            return $this->success(
                'Data skor kesehatan berhasil diambil',
                $user->healthScores()->orderByDesc('score_date')->get()
            );
        }

        $summary = $this->buildSummary($user);

        $insight = null;
        if ($summary['has_food']) {
            $insight = $this->saveScore($user, $summary)->ai_insight;
        }
        $summary['insight'] = $insight;

        $history = $user->healthScores()
            ->orderByDesc('score_date')
            ->limit(7)
            ->get();

        return view('health-scores.index', compact('summary', 'history'));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        $summary = $this->buildSummary($user);

        if (! $summary['has_food']) {
            $message = 'Belum ada makanan yang dicatat hari ini, jadi insight belum bisa dibuat.';

            if ($request->wantsJson()) {
                return $this->error($message, 422);
            }

            return redirect()->route('health-scores.index')->with('warning', $message);
        }

        $insight = $this->generateInsight($user, $summary);
        $score = $this->saveScore($user, $summary, $insight);

        $message = $insight !== null
            ? 'Skor dan insight AI berhasil diperbarui!'
            : 'Skor diperbarui, tetapi insight AI gagal dibuat. Coba lagi beberapa saat lagi.';

        if ($request->wantsJson()) {
            return $this->success($message, $score);
        }

        return redirect()->route('health-scores.index')->with($insight !== null ? 'success' : 'warning', $message);
    }

    private function calculateCalorieTarget(User $user): int
    {
        if ($user->weight_kg && $user->height_cm) {
            // Estimated TDEE using Mifflin-St Jeor baseline with light-moderate activity factor
            $estimated = (int) round((10 * $user->weight_kg + 6.25 * $user->height_cm - 150) * 1.375);
            return max(1200, min(3500, $estimated));
        }

        return self::DEFAULT_CALORIE_TARGET;
    }

    private function buildSummary(User $user): array
    {
        $today = now()->toDateString();
        $calorieTarget = $this->calculateCalorieTarget($user);

        $foodLogs = $user->foodLogs()->whereDate('consumed_at', $today)->get();
        $exerciseLogs = $user->exerciseLogs()->whereDate('exercised_at', $today)->get();

        $hasFood = $foodLogs->isNotEmpty();
        $hasExercise = $exerciseLogs->isNotEmpty();

        $caloriesIn = (int) $foodLogs->sum('calories');
        $caloriesBurned = (int) $exerciseLogs->sum('calories_burned');

        $nutritionScore = $hasFood
            ? (int) round(max(0, 100 - abs($calorieTarget - $caloriesIn) / 20))
            : null;

        $activityScore = $hasExercise
            ? (int) round(min(100, $caloriesBurned / 10))
            : null;

        $scores = array_filter([$nutritionScore, $activityScore], fn ($s) => $s !== null);
        $totalScore = $scores ? (int) round(array_sum($scores) / count($scores)) : null;

        return [
            'has_food' => $hasFood,
            'calories_in' => $caloriesIn,
            'calories_target' => $calorieTarget,
            'calories_burned' => $caloriesBurned,
            'nutrition_score' => $nutritionScore,
            'activity_score' => $activityScore,
            'total_score' => $totalScore,
            'macros' => [
                'fat' => (int) $foodLogs->sum(fn ($log) => $log->nutrition_details['fat_g'] ?? 0),
                'protein' => (int) $foodLogs->sum(fn ($log) => $log->nutrition_details['protein_g'] ?? 0),
                'carbs' => (int) $foodLogs->sum(fn ($log) => $log->nutrition_details['carbs_g'] ?? 0),
            ],
            'tip' => $this->quickTip($hasFood, $caloriesIn, $calorieTarget),
        ];
    }

    private function saveScore(User $user, array $s, ?string $insight = null): HealthScore
    {
        $values = [
            'total_score' => $s['total_score'],
            'nutrition_score' => $s['nutrition_score'],
            'activity_score' => $s['activity_score'],
            'calories_in' => $s['calories_in'],
            'calories_burned' => $s['calories_burned'],
        ];

        if ($insight !== null) {
            $values['ai_insight'] = $insight;
        }

        return $user->healthScores()->updateOrCreate(
            ['score_date' => now()->toDateString()],
            $values
        );
    }

    private function quickTip(bool $hasFood, int $caloriesIn, int $calorieTarget): string
    {
        if (! $hasFood) {
            return 'Belum ada makanan yang dicatat hari ini. Catat makananmu di Food Tracker supaya skor bisa dihitung.';
        }

        $diff = $caloriesIn - $calorieTarget;

        if ($diff < -400) {
            return 'Asupanmu sekitar ' . number_format(abs($diff)) . ' kkal di bawah target harian. Tambahkan makanan bergizi seperti nasi, telur, atau sayur agar energimu cukup.';
        }

        if ($diff > 400) {
            return 'Asupanmu sekitar ' . number_format($diff) . ' kkal di atas target harian. Kurangi camilan manis atau gorengan untuk sisa hari ini.';
        }

        return 'Asupan kalorimu sudah dekat dengan target harian. Pertahankan pola makan seimbang ini.';
    }

    private function generateInsight(User $user, array $s): ?string
    {
        $key = config('services.gemini.key');

        if (! $key) {
            Log::warning('GEMINI_API_KEY is not set, skipping health insight.');
            return null;
        }

        $bodyInfo = ($user->weight_kg && $user->height_cm)
            ? "berat badan {$user->weight_kg} kg, tinggi badan {$user->height_cm} cm, "
            : '';

        $prompt = 'Kamu adalah asisten kesehatan di aplikasi ASVIS. Data pengguna hari ini: '
            . $bodyInfo
            . "total kalori {$s['calories_in']} kkal (target {$s['calories_target']} kkal), "
            . "protein {$s['macros']['protein']} g, karbohidrat {$s['macros']['carbs']} g, lemak {$s['macros']['fat']} g, "
            . "skor nutrisi {$s['nutrition_score']} dari 100. ";

        if ($s['activity_score'] !== null) {
            $prompt .= "Kalori terbakar dari olahraga {$s['calories_burned']} kkal. ";
        }

        $prompt .= 'Tulis insight singkat dalam Bahasa Indonesia (2-3 kalimat, tanpa markdown, tanpa daftar) '
            . 'tentang pola makan hari ini dan satu saran konkret untuk besok. Jangan memberikan diagnosis medis.';

        try {
            $response = Http::timeout(15)
                ->retry(2, 1500, throw: false)
                ->withHeaders(['x-goog-api-key' => $key])
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model', 'gemini-2.5-flash') . ':generateContent',
                    ['contents' => [['parts' => [['text' => $prompt]]]]]
                );

            if ($response->successful()) {
                $text = trim(
                    collect($response->json('candidates.0.content.parts', []))
                        ->pluck('text')
                        ->filter()
                        ->implode(' ')
                );

                if ($text !== '') {
                    return $text;
                }
            }

            Log::warning('Gemini insight request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini insight call threw an exception: ' . $e->getMessage());
        }

        return null;
    }
}