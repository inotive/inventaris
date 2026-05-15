<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Online
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $expireAt = Carbon::now()->addMinutes(2);

            // Simpan status online di cache
            Cache::put('user-' . Auth::id() . '-is-online', true, $expireAt);

            // Update last_seen di database
            User::where('id', Auth::id())->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
