<?php

namespace App\Http\Controllers;

use App\Services\PublicStatsService;
use Illuminate\Http\JsonResponse;

class PublicStatsController extends Controller
{
    public function __invoke(PublicStatsService $stats): JsonResponse
    {
        return response()->json($stats->all());
    }
}
