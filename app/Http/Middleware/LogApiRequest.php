<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        ApiRequestLog::query()->create([
            'personal_access_token_id' => $token?->id,
            'endpoint' => '/'.$request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
