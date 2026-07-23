<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAbility
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->tokenCan($ability)) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        return $next($request);
    }
}
