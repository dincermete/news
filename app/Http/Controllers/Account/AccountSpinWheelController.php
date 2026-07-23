<?php

namespace App\Http\Controllers\Account;

use App\Enums\SpinPrizeType;
use App\Exceptions\InsufficientSpinCreditsException;
use App\Exceptions\NoAvailableSpinPrizesException;
use App\Http\Controllers\Controller;
use App\Models\SpinWheelPrize;
use App\Models\WalletTopupPackage;
use App\Services\SeoMetaService;
use App\Services\SpinWheelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountSpinWheelController extends Controller
{
    /**
     * Active prizes in a stable order shared by the wheel UI and spin() response.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SpinWheelPrize>
     */
    public static function orderedPrizes()
    {
        return SpinWheelPrize::query()
            ->available()
            ->orderBy('id')
            ->get();
    }

    public function index(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();
        $prizes = self::orderedPrizes();

        $totalWinnings = (float) DB::table('spin_wheel_spins')
            ->join('spin_wheel_prizes', 'spin_wheel_prizes.id', '=', 'spin_wheel_spins.spin_wheel_prize_id')
            ->where('spin_wheel_spins.user_id', $user->id)
            ->where('spin_wheel_prizes.type', SpinPrizeType::Balance->value)
            ->sum('spin_wheel_prizes.value');

        $packages = WalletTopupPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('amount')
            ->get();

        $recentSpins = $user->spinWheelSpins()
            ->with('prize')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('account.spin-wheel', [
            'meta' => $seo->forDefault(),
            'prizes' => $prizes,
            'spinCredits' => $user->spinCreditBalance(),
            'totalWinnings' => round($totalWinnings, 2),
            'packages' => $packages,
            'recentSpins' => $recentSpins,
        ]);
    }

    public function spin(Request $request, SpinWheelService $spinWheel): JsonResponse
    {
        $user = $request->user();
        $prizes = self::orderedPrizes();

        if ($prizes->isEmpty()) {
            return response()->json([
                'message' => NoAvailableSpinPrizesException::make()->getMessage(),
            ], 422);
        }

        try {
            $spin = $spinWheel->spin($user);
        } catch (InsufficientSpinCreditsException|NoAvailableSpinPrizesException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $segmentIndex = $prizes->search(fn (SpinWheelPrize $prize): bool => $prize->id === $spin->spin_wheel_prize_id);

        if ($segmentIndex === false) {
            $segmentIndex = 0;
        }

        $prize = $spin->prize;

        return response()->json([
            'segment_index' => (int) $segmentIndex,
            'spin_credits' => $user->fresh()->spinCreditBalance(),
            'prize' => [
                'id' => $prize->id,
                'name' => $prize->name,
                'type' => $prize->type?->value,
                'value' => $prize->value !== null ? (float) $prize->value : null,
                'label' => $prize->type === SpinPrizeType::Balance
                    ? number_format((float) $prize->value, 2, ',', '.').' ₺'
                    : ($prize->name ?: 'Boş'),
            ],
            'prizes' => $prizes->map(fn (SpinWheelPrize $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values(),
        ]);
    }
}
