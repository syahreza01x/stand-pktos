<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admin has universal override privileges
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user is assigned to any currently open shift
        $hasOpenShift = Shift::query()
            ->where('status', 'open')
            ->whereHas('cashiers', function ($q) use ($user): void {
                $q->where('users.id', $user->id);
            })
            ->exists();

        if (! $hasOpenShift) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak sedang ditugaskan pada shift yang berstatus Open saat ini.',
                ], 403);
            }

            return redirect()->route('cashier.dashboard')->with('error', 'Anda tidak sedang ditugaskan pada shift yang berstatus Open saat ini.');
        }

        return $next($request);
    }
}
