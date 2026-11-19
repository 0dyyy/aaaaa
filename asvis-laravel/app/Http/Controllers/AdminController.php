<?php

namespace App\Http\Controllers;

use App\Models\ExerciseLog;
use App\Models\FoodLog;
use App\Models\HealthScore;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_food_logs' => FoodLog::count(),
            'total_exercise_logs' => ExerciseLog::count(),
            'avg_health_score' => (int) round(HealthScore::avg('total_score') ?? 0),
        ];

        $users = User::withCount(['foodLogs', 'exerciseLogs'])
            ->orderByDesc('created_at')
            ->get();

        $recentFoodLogs = FoodLog::with('user')
            ->orderByDesc('consumed_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'users', 'recentFoodLogs'));
    }

    public function toggleRole(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('warning', 'Anda tidak dapat mengubah peran akun Anda sendiri.');
        }

        $user->update([
            'role' => $user->role === 'admin' ? 'user' : 'admin',
        ]);

        return back()->with('success', "Peran {$user->name} berhasil diubah menjadi {$user->role}.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('warning', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}