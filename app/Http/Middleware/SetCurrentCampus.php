<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCurrentCampus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && ! session()->has('current_campus_id')) {
            $firstCampus = $user->hasRole('Manager') ? $user->organization?->campuses()->first() : $user->campuses()->first();
            if ($firstCampus) {
                session(['current_campus_id' => $firstCampus->id]);
            }
        }
        return $next($request);
    }
}
