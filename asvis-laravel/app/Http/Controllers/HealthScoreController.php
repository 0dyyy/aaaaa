<?php

namespace App\Http\Controllers;

use App\Models\HealthScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthScoreController extends Controller
{
    /**
     * Display the user's health score history and today's dynamic chart data.
     */
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 1. Fetch Score History
        $healthScores = $user->healthScores()
            ->orderBy('score_date', 'desc')
            ->get();
            
        $todayScore = $healthScores->where('score_date', $today)->first();

        // 2. Fetch Today's Food Logs for the Chart
        $todayLogs = $user->foodLogs()
            ->whereDate('consumed_at', $today)
            ->get();

        // 3. Sum up the macros from the JSON array
        $fat = $todayLogs->sum(fn($log) => $log->nutrition_details['fat_g'] ?? 0);
        $protein = $todayLogs->sum(fn($log) => $log->nutrition_details['protein_g'] ?? 0);
        $carbs = $todayLogs->sum(fn($log) => $log->nutrition_details['carbs_g'] ?? 0);
        
        // Note: Our AI prompt only asked for Fat, Protein, and Carbs. 
        // Fiber (Serat) and Sodium (Natrium) default to 0 for now.
        $fiber = 0; 
        $sodium = 0;

        // Matches your chart labels: ['Lemak', 'Protein', 'Serat', 'Karbohidrat', 'Natrium']
        $macroData = [$fat, $protein, $fiber, $carbs, $sodium];

        return view('health-scores.index', compact('healthScores', 'todayScore', 'macroData'));
    }

    /**
     * Calculate and generate the health score for today.
     */
    public function generate()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Calculate Calories In from FoodLogs today
        $caloriesIn = $user->foodLogs()
            ->whereDate('consumed_at', $today)
            ->sum('calories');

        // Calculate Calories Burned from ExerciseLogs today
        $caloriesBurned = $user->exerciseLogs()
            ->whereDate('exercised_at', $today)
            ->sum('calories_burned');

        // Scoring Algorithm
        $nutritionScore = max(0, 100 - (abs(2000 - $caloriesIn) / 20));
        $activityScore = min(100, ($caloriesBurned / 10));
        $totalScore = round(($nutritionScore + $activityScore) / 2);

        // AI Insight Placeholder
        $insights = [
            "Keseimbangan nutrisi sangat baik, pertahankan porsi makanmu hari ini!",
            "Aktivitas fisikmu luar biasa, namun pastikan asupan kalori cukup untuk pemulihan.",
            "Skor hari ini cukup rendah, coba perbanyak gerak dan kurangi camilan manis.",
            "Perhatikan kualitas udara sebelum berolahraga besok untuk menjaga kesehatan paru-paru."
        ];
        $aiInsight = $insights[array_rand($insights)];

        // Create or Update today's Health Score
        $user->healthScores()->updateOrCreate(
            ['score_date' => $today],
            [
                'total_score' => (int) $totalScore,
                'nutrition_score' => (int) $nutritionScore,
                'activity_score' => (int) $activityScore,
                'calories_in' => (int) $caloriesIn,
                'calories_burned' => (int) $caloriesBurned,
                'ai_insight' => $aiInsight,
            ]
        );

        return redirect()->back()->with('success', 'Skor kesehatan harian berhasil diperbarui dan dihitung ulang!');
    }
}