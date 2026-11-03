<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\ShopeePayMerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QRISVerificationController extends Controller
{
    public function __construct(
        protected ShopeePayMerchantService $shopeePayService
    ) {}

    /**
     * Get live incoming QRIS mutations from ShopeePay Merchant.
     */
    public function liveMutations(Request $request): JsonResponse
    {
        $force = $request->boolean('refresh', false);
        $result = $this->shopeePayService->fetchLiveTransactions(10, forceRefresh: $force);

        return response()->json($result);
    }

    /**
     * Check if a payment matching a specific amount has been received recently.
     */
    public function checkMatch(Request $request): JsonResponse
    {
        $amount = (float) $request->input('amount', 0);

        if ($amount <= 0) {
            return response()->json([
                'matched' => false,
                'message' => 'Nominal transaksi tidak valid.',
                'transaction' => null,
                'live_list' => [],
            ], 422);
        }

        $tolerance = (int) $request->input('tolerance_minutes', 2);
        $result = $this->shopeePayService->checkRecentPayment($amount, toleranceMinutes: $tolerance > 0 ? $tolerance : 2);

        return response()->json($result);
    }
}
