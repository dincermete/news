<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\FakeOrderNotificationName;
use App\Models\FakeOrderNotificationTemplate;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class FakeNotificationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $template = FakeOrderNotificationTemplate::query()
            ->active()
            ->inRandomOrder()
            ->first();

        $person = FakeOrderNotificationName::query()
            ->inRandomOrder()
            ->first();

        if ($template === null || $person === null) {
            return response()->json([
                'message' => null,
            ], 404);
        }

        $product = Site::query()
            ->where('status', SiteStatus::Active)
            ->inRandomOrder()
            ->value('domain') ?? 'tanıtım paketi';

        $message = str_replace(
            ['{isim}', '{sehir}', '{urun}'],
            [$person->name, $person->city, $product],
            $template->message_template,
        );

        return response()->json([
            'message' => $message,
            'display_interval_seconds' => max(5, (int) $template->display_interval_seconds),
            'name' => $person->name,
            'city' => $person->city,
        ]);
    }
}
