<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeePayMerchantService
{
    /**
     * Get the active ShopeePay merchant cookie.
     */
    public function getCookie(): ?string
    {
        return Setting::get('shopeepay_cookie') ?: config('services.shopeepay.cookie');
    }

    /**
     * Get the active ShopeePay metadata token.
     */
    public function getToken(): ?string
    {
        return Setting::get('shopeepay_token') ?: config('services.shopeepay.token');
    }

    /**
     * Get API endpoint.
     */
    public function getEndpoint(): string
    {
        return (string) (Setting::get('shopeepay_endpoint') ?: config('services.shopeepay.endpoint', 'https://shopeepay.shopee.co.id/merchant/v1/partner-web/get-transaction-list'));
    }

    /**
     * Check whether credentials are configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->getCookie()) && ! empty($this->getToken());
    }

    /**
     * Test connection to ShopeePay Merchant API.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     store_name?: string|null,
     *     merchant_id?: int|string|null,
     *     total_net_sales?: string|null,
     *     total_count?: int|null
     * }
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial ShopeePay belum dikonfigurasi. Harap masukkan Cookie dan Token.',
            ];
        }

        try {
            $data = $this->requestTransactionList(1);

            if (($data['code'] ?? null) === 0) {
                $firstItem = $data['data']['list'][0] ?? null;
                $storeName = $firstItem['storeName'] ?? 'Stand GenBI';
                $merchantId = $firstItem['merchantId'] ?? null;
                $totalNetSales = (string) $this->parseAmount($data['data']['totalNetSales'] ?? '0');
                $totalCount = (int) ($data['data']['totalCompletedCount'] ?? 0);

                return [
                    'success' => true,
                    'message' => 'Berhasil terhubung ke ShopeePay Merchant!',
                    'store_name' => $storeName,
                    'merchant_id' => $merchantId,
                    'total_net_sales' => $totalNetSales,
                    'total_count' => $totalCount,
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal terhubung. Respon Shopee: '.($data['msg'] ?? 'Status '.json_encode($data)),
            ];
        } catch (Exception $e) {
            Log::error('ShopeePay Merchant testConnection failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan jaringan: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Fetch live transactions from ShopeePay Merchant.
     *
     * @return array{
     *     success: bool,
     *     message?: string,
     *     store_name: string,
     *     total_net_sales: float,
     *     formatted_total_net_sales: string,
     *     total_completed_count: int,
     *     transactions: array<int, array{
     *         transaction_id: string,
     *         amount: float,
     *         formatted_amount: string,
     *         status: int,
     *         status_label: string,
     *         is_success: bool,
     *         created_at_timestamp: int,
     *         formatted_time: string,
     *         time_ago: string,
     *         store_name: string
     *     }>,
     *     fetched_at: string
     * }
     */
    public function fetchLiveTransactions(int $pageSize = 15, bool $forceRefresh = false): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial ShopeePay belum dikonfigurasi.',
                'store_name' => '-',
                'total_net_sales' => 0.0,
                'formatted_total_net_sales' => 'Rp0',
                'total_completed_count' => 0,
                'transactions' => [],
                'fetched_at' => now()->format('H:i:s'),
            ];
        }

        $cacheKey = 'shopeepay_live_transactions_'.$pageSize;

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var array $cached */
            $cached = Cache::get($cacheKey);

            return $cached;
        }

        try {
            $response = $this->requestTransactionList($pageSize);

            if (($response['code'] ?? null) !== 0) {
                return [
                    'success' => false,
                    'message' => $response['msg'] ?? 'Gagal mengambil data dari ShopeePay',
                    'store_name' => '-',
                    'total_net_sales' => 0.0,
                    'formatted_total_net_sales' => 'Rp0',
                    'total_completed_count' => 0,
                    'transactions' => [],
                    'fetched_at' => now()->format('H:i:s'),
                ];
            }

            $data = $response['data'] ?? [];
            $rawList = (array) ($data['list'] ?? []);
            $totalSales = $this->parseAmount($data['totalNetSales'] ?? 0);
            $totalCompleted = (int) ($data['totalCompletedCount'] ?? count($rawList));

            $formattedList = [];
            $storeName = 'Stand GenBI';

            foreach ($rawList as $item) {
                $amount = $this->parseAmount($item['amount'] ?? 0);
                $createTime = (int) ($item['createTime'] ?? time());
                $status = (int) ($item['status'] ?? 0);
                $isSuccess = ($status === 3);

                if (! empty($item['storeName'])) {
                    $storeName = (string) $item['storeName'];
                }

                $formattedList[] = [
                    'transaction_id' => (string) ($item['displayTransactionId'] ?? $item['transactionId'] ?? ''),
                    'amount' => $amount,
                    'formatted_amount' => 'Rp'.number_format($amount, 0, ',', '.'),
                    'status' => $status,
                    'status_label' => $isSuccess ? 'BERHASIL' : 'PENDING',
                    'is_success' => $isSuccess,
                    'created_at_timestamp' => $createTime,
                    'formatted_time' => Carbon::createFromTimestamp($createTime)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                    'time_ago' => Carbon::createFromTimestamp($createTime)->timezone('Asia/Jakarta')->diffForHumans(),
                    'store_name' => (string) ($item['storeName'] ?? $storeName),
                ];
            }

            $result = [
                'success' => true,
                'store_name' => $storeName,
                'total_net_sales' => $totalSales,
                'formatted_total_net_sales' => 'Rp'.number_format($totalSales, 0, ',', '.'),
                'total_completed_count' => $totalCompleted,
                'transactions' => $formattedList,
                'fetched_at' => now()->timezone('Asia/Jakarta')->format('H:i:s'),
            ];

            // Cache for 10 seconds to avoid spamming Shopee on frequent UI polls
            Cache::put($cacheKey, $result, now()->addSeconds(10));

            return $result;
        } catch (Exception $e) {
            Log::error('ShopeePay fetchLiveTransactions failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Koneksi ke ShopeePay terganggu: '.$e->getMessage(),
                'store_name' => '-',
                'total_net_sales' => 0.0,
                'formatted_total_net_sales' => 'Rp0',
                'total_completed_count' => 0,
                'transactions' => [],
                'fetched_at' => now()->format('H:i:s'),
            ];
        }
    }

    /**
     * Check if an incoming payment matching targetAmount exists within the tolerance minutes window.
     *
     * @return array{
     *     matched: bool,
     *     message: string,
     *     transaction: array<string, mixed>|null,
     *     live_list: array<int, array<string, mixed>>
     * }
     */
    public function checkRecentPayment(float $targetAmount, int $toleranceMinutes = 2): array
    {
        $liveData = $this->fetchLiveTransactions(20, forceRefresh: true);

        if (! $liveData['success']) {
            return [
                'matched' => false,
                'message' => $liveData['message'] ?? 'Gagal memeriksa mutasi ShopeePay.',
                'transaction' => null,
                'live_list' => [],
            ];
        }

        $now = now()->timestamp;
        $minTime = $now - ($toleranceMinutes * 60);
        $maxTime = $now + 120; // 2 minutes in the future to tolerate server clock drift

        foreach ($liveData['transactions'] as $tx) {
            // Must be successful
            if (! ($tx['is_success'] ?? false)) {
                continue;
            }

            // Must match target amount
            if (abs((float) $tx['amount'] - $targetAmount) > 0.01) {
                continue;
            }

            // Must be within tolerance window (-2m to +2m)
            $txTime = (int) ($tx['created_at_timestamp'] ?? 0);
            if ($txTime < $minTime || $txTime > $maxTime) {
                continue;
            }

            // Must not have been already recorded by an earlier finalized order
            $txId = (string) ($tx['transaction_id'] ?? '');
            if ($txId !== '') {
                $isAlreadyUsed = Transaction::where('payment_method', 'qris')
                    ->where('status', '!=', 'voided')
                    ->where('notes', 'like', "%[QRIS: {$txId}]%")
                    ->exists();

                if ($isAlreadyUsed) {
                    continue;
                }
            }

            return [
                'matched' => true,
                'message' => 'Pembayaran '.$tx['formatted_amount'].' terverifikasi masuk ('.$tx['formatted_time'].')',
                'transaction' => $tx,
                'live_list' => $liveData['transactions'] ?? [],
            ];
        }

        return [
            'matched' => false,
            'message' => 'Belum ada pembayaran Rp'.number_format($targetAmount, 0, ',', '.').' baru masuk (dicek '.$toleranceMinutes.' menit terakhir). Minta pembeli scan & klik Cek lagi.',
            'transaction' => null,
            'live_list' => $liveData['transactions'] ?? [],
        ];
    }

    /**
     * Send HTTP POST request to ShopeePay partner-web get-transaction-list.
     */
    protected function requestTransactionList(int $pageSize = 10): array
    {
        $cookie = $this->getCookie();
        $token = $this->getToken();
        $endpoint = $this->getEndpoint();

        $payload = [
            'data' => [
                'metadata' => [
                    'token' => $token,
                    'language' => 'id',
                    'timezone' => 'Asia/Jakarta',
                ],
                'pageSize' => $pageSize,
                'filter' => [
                    'startTime' => time() - (86400 * 30), // Last 30 days
                    'endTime' => time() + 3600,
                    'serviceList' => [1, 3],
                ],
                'sorter' => [
                    'field' => 'createTime',
                    'order' => 'descend',
                ],
                'next_position' => '',
            ],
        ];

        $response = Http::withHeaders([
            'Cookie' => $cookie,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
            'Origin' => 'https://partner.shopee.co.id',
            'Referer' => 'https://partner.shopee.co.id/',
            'Accept' => 'application/json, text/plain, */*',
            'Content-Type' => 'application/json',
        ])
            ->timeout(12)
            ->post($endpoint, $payload);

        return $response->json() ?? [];
    }

    /**
     * Parse amount returned by ShopeePay API into float.
     * ShopeePay Merchant API for Indonesia formats numbers using '.' as thousands separator (e.g. "5.000", "5.002", "150.000").
     */
    public function parseAmount(mixed $rawAmount): float
    {
        if (is_numeric($rawAmount) && ! is_string($rawAmount)) {
            return (float) $rawAmount;
        }

        $str = trim((string) $rawAmount);
        if ($str === '') {
            return 0.0;
        }

        if (str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);

            return (float) $str;
        }

        if (str_contains($str, '.')) {
            $str = str_replace('.', '', $str);

            return (float) $str;
        }

        return (float) $str;
    }
}
