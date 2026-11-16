<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login, atau sudah login tapi bukan admin -> Tolak!
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Anda bukan Admin.');
            
            // Alternatif kalau mau dilempar balik ke dashboard user biasa:
            // return redirect('/dashboard')->with('error', 'Akses ditolak!');
        }

        return $next($request);
    }
}