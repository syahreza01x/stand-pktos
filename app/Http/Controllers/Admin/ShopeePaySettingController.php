<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ShopeePayMerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopeePaySettingController extends Controller
{
    public function __construct(
        protected ShopeePayMerchantService $shopeePayService
    ) {}

    public function index(): View
    {
        $cookie = Setting::get('shopeepay_cookie') ?: config('services.shopeepay.cookie');
        $token = Setting::get('shopeepay_token') ?: config('services.shopeepay.token');
        $endpoint = Setting::get('shopeepay_endpoint') ?: config('services.shopeepay.endpoint', 'https://shopeepay.shopee.co.id/merchant/v1/partner-web/get-transaction-list');

        $testStatus = null;
        if ($this->shopeePayService->isConfigured()) {
            $testStatus = $this->shopeePayService->testConnection();
        }

        $liveTransactions = $this->shopeePayService->fetchLiveTransactions(10, forceRefresh: true);

        return view('admin.settings.qris', compact('cookie', 'token', 'endpoint', 'testStatus', 'liveTransactions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'shopeepay_cookie' => ['required', 'string'],
            'shopeepay_token' => ['required', 'string'],
            'shopeepay_endpoint' => ['nullable', 'url'],
        ]);

        Setting::set('shopeepay_cookie', trim($request->input('shopeepay_cookie')), 'Session Cookie ShopeePay Merchant');
        Setting::set('shopeepay_token', trim($request->input('shopeepay_token')), 'Metadata Token ShopeePay Merchant');

        if ($request->filled('shopeepay_endpoint')) {
            Setting::set('shopeepay_endpoint', trim($request->input('shopeepay_endpoint')), 'Endpoint ShopeePay Merchant');
        }

        return redirect()->route('admin.settings.qris')->with('success', 'Pengaturan ShopeePay Merchant berhasil disimpan!');
    }

    public function test(): JsonResponse
    {
        $status = $this->shopeePayService->testConnection();

        return response()->json($status);
    }
}
