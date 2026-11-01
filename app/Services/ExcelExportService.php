<?php

namespace App\Services;

use App\Models\EventDay;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    /**
     * Export Daily Report (Shift 1, Shift 2, & Total Hari) as an Excel-compatible spreadsheet.
     */
    public function exportDailyReport(EventDay $day): StreamedResponse
    {
        $report = $this->reportingService->getDailyReport($day);
        $fileName = 'Laporan_Harian_'.$day->day_name.'_'.$day->event_date->format('Ymd').'.xls';

        return response()->streamDownload(function () use ($report, $day): void {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
            echo '<x:Name>Laporan '.htmlspecialchars($day->day_name).'</x:Name>';
            echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>';
            echo 'body { font-family: Calibri, sans-serif; font-size: 11pt; }';
            echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }';
            echo 'th { background-color: #1e293b; color: #ffffff; border: 1px solid #475569; padding: 8px; text-align: center; }';
            echo 'td { border: 1px solid #cbd5e1; padding: 6px 8px; }';
            echo '.header-title { font-size: 16pt; font-weight: bold; color: #0f172a; margin-bottom: 4px; }';
            echo '.sub-title { font-size: 12pt; color: #475569; margin-bottom: 12px; }';
            echo '.text-right { text-align: right; }';
            echo '.text-center { text-align: center; }';
            echo '.font-bold { font-weight: bold; }';
            echo '.bg-total { background-color: #f1f5f9; font-weight: bold; }';
            echo '.bg-s1 { background-color: #e0f2fe; }';
            echo '.bg-s2 { background-color: #fef3c7; }';
            echo '</style></head><body>';

            echo '<div class="header-title">EVENT SALES MANAGEMENT SYSTEM — STAND PKTOS</div>';
            echo '<div class="sub-title">Laporan Penjualan: Hari '.htmlspecialchars($day->day_name).' ('.$day->event_date->format('d/m/Y').')</div>';

            // Tabel 1: Shift 1
            echo '<h3>1. Ringkasan Shift 1 (Pagi)</h3>';
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>No</th><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Harga Satuan (Rp)</th><th>Kuantitas Terjual</th><th>Subtotal Penjualan (Rp)</th>';
            echo '</tr></thead><tbody>';

            if (! empty($report['shift1']['items'])) {
                foreach ($report['shift1']['items'] as $index => $item) {
                    echo '<tr>';
                    echo '<td class="text-center">'.($index + 1).'</td>';
                    echo '<td class="text-center">'.($item['product_id']).'</td>';
                    echo '<td>'.htmlspecialchars($item['product_name']).'</td>';
                    echo '<td class="text-center">'.htmlspecialchars($item['category'] ?? '-').'</td>';
                    echo '<td class="text-right">'.number_format($item['unit_price'], 0, ',', '.').'</td>';
                    echo '<td class="text-center font-bold">'.$item['quantity'].'</td>';
                    echo '<td class="text-right font-bold">'.number_format($item['subtotal'], 0, ',', '.').'</td>';
                    echo '</tr>';
                }
                echo '<tr class="bg-total"><td colspan="5" class="text-right">TOTAL SHIFT 1</td>';
                echo '<td class="text-center">'.$report['shift1']['total_quantity'].'</td>';
                echo '<td class="text-right">Rp '.number_format($report['shift1']['total_revenue'], 0, ',', '.').'</td></tr>';
            } else {
                echo '<tr><td colspan="7" class="text-center">Belum ada data transaksi Shift 1</td></tr>';
            }
            echo '</tbody></table>';

            // Tabel 2: Shift 2
            echo '<h3>2. Ringkasan Shift 2 (Sore)</h3>';
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>No</th><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Harga Satuan (Rp)</th><th>Kuantitas Terjual</th><th>Subtotal Penjualan (Rp)</th>';
            echo '</tr></thead><tbody>';

            if (! empty($report['shift2']['items'])) {
                foreach ($report['shift2']['items'] as $index => $item) {
                    echo '<tr>';
                    echo '<td class="text-center">'.($index + 1).'</td>';
                    echo '<td class="text-center">'.($item['product_id']).'</td>';
                    echo '<td>'.htmlspecialchars($item['product_name']).'</td>';
                    echo '<td class="text-center">'.htmlspecialchars($item['category'] ?? '-').'</td>';
                    echo '<td class="text-right">'.number_format($item['unit_price'], 0, ',', '.').'</td>';
                    echo '<td class="text-center font-bold">'.$item['quantity'].'</td>';
                    echo '<td class="text-right font-bold">'.number_format($item['subtotal'], 0, ',', '.').'</td>';
                    echo '</tr>';
                }
                echo '<tr class="bg-total"><td colspan="5" class="text-right">TOTAL SHIFT 2</td>';
                echo '<td class="text-center">'.$report['shift2']['total_quantity'].'</td>';
                echo '<td class="text-right">Rp '.number_format($report['shift2']['total_revenue'], 0, ',', '.').'</td></tr>';
            } else {
                echo '<tr><td colspan="7" class="text-center">Belum ada data transaksi Shift 2</td></tr>';
            }
            echo '</tbody></table>';

            // Tabel 3: Total Hari (Kombinasi Shift 1 + Shift 2)
            echo '<h3>3. TOTAL PENJUALAN HARIAN (SHIFT 1 + SHIFT 2)</h3>';
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>No</th><th>Nama Produk</th><th>Harga Satuan (Rp)</th><th class="bg-s1">Qty Shift 1</th><th class="bg-s2">Qty Shift 2</th><th>Total Qty Harian</th><th>Total Pendapatan Harian (Rp)</th>';
            echo '</tr></thead><tbody>';

            foreach ($report['daily_total']['items'] as $index => $dItem) {
                echo '<tr>';
                echo '<td class="text-center">'.($index + 1).'</td>';
                echo '<td class="font-bold">'.htmlspecialchars($dItem['product_name']).'</td>';
                echo '<td class="text-right">'.number_format($dItem['unit_price'], 0, ',', '.').'</td>';
                echo '<td class="text-center">'.$dItem['quantity_s1'].'</td>';
                echo '<td class="text-center">'.$dItem['quantity_s2'].'</td>';
                echo '<td class="text-center font-bold">'.$dItem['total_quantity'].'</td>';
                echo '<td class="text-right font-bold">'.number_format($dItem['total_subtotal'], 0, ',', '.').'</td>';
                echo '</tr>';
            }
            echo '<tr class="bg-total" style="background-color: #dbeafe; font-size: 12pt;">';
            echo '<td colspan="5" class="text-right">GRAND TOTAL HARI '.strtoupper(htmlspecialchars($day->day_name)).'</td>';
            echo '<td class="text-center">'.$report['daily_total']['total_quantity'].' unit</td>';
            echo '<td class="text-right">Rp '.number_format($report['daily_total']['total_revenue'], 0, ',', '.').'</td>';
            echo '</tr>';
            echo '</tbody></table>';

            echo '</body></html>';
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Export 5-Day Overall Recap.
     */
    public function exportFiveDayRecap(): StreamedResponse
    {
        $recap = $this->reportingService->getFiveDayOverallRecap();
        $fileName = 'Rekap_Penjualan_5_Hari_Stand_PKTOS_'.date('Ymd_His').'.xls';

        return response()->streamDownload(function () use ($recap): void {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<style>';
            echo 'body { font-family: Calibri, sans-serif; font-size: 11pt; }';
            echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 24px; }';
            echo 'th { background-color: #0f172a; color: #ffffff; border: 1px solid #334155; padding: 8px; text-align: center; }';
            echo 'td { border: 1px solid #cbd5e1; padding: 6px 8px; }';
            echo '.text-right { text-align: right; }';
            echo '.text-center { text-align: center; }';
            echo '.font-bold { font-weight: bold; }';
            echo '.bg-total { background-color: #f1f5f9; font-weight: bold; }';
            echo '</style></head><body>';

            echo '<h2>REKAPITULASI PENJUALAN 5 HARI — STAND PKTOS</h2>';

            // Ringkasan Harian
            echo '<h3>1. Pendapatan & Unit per Hari</h3>';
            echo '<table>';
            echo '<thead><tr><th>Hari</th><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Unit Terjual</th><th>Total Pendapatan (Rp)</th></tr></thead><tbody>';
            foreach ($recap['daily_summary'] as $ds) {
                echo '<tr>';
                echo '<td class="font-bold">'.htmlspecialchars($ds['day']->day_name).'</td>';
                echo '<td class="text-center">'.$ds['day']->event_date->format('d/m/Y').'</td>';
                echo '<td class="text-center">'.$ds['tx_count'].'</td>';
                echo '<td class="text-center font-bold">'.$ds['units'].'</td>';
                echo '<td class="text-right font-bold">'.number_format($ds['revenue'], 0, ',', '.').'</td>';
                echo '</tr>';
            }
            echo '<tr class="bg-total" style="background-color: #e2e8f0; font-size: 12pt;">';
            echo '<td colspan="2">TOTAL 5 HARI</td>';
            echo '<td class="text-center">'.$recap['total_transactions_5_days'].'</td>';
            echo '<td class="text-center">'.$recap['total_units_5_days'].'</td>';
            echo '<td class="text-right font-bold">Rp '.number_format($recap['total_revenue_5_days'], 0, ',', '.').'</td>';
            echo '</tr></tbody></table>';

            // Produk Terlaris
            echo '<h3>2. Peringkat Produk Terlaris (Ranking)</h3>';
            echo '<table>';
            echo '<thead><tr><th>Peringkat</th><th>Nama Produk</th><th>Total Unit Terjual</th><th>Total Omzet (Rp)</th></tr></thead><tbody>';
            foreach ($recap['products_ranking'] as $idx => $pr) {
                echo '<tr>';
                echo '<td class="text-center font-bold">#'.($idx + 1).'</td>';
                echo '<td class="font-bold">'.htmlspecialchars($pr->product_name_snapshot).'</td>';
                echo '<td class="text-center font-bold">'.$pr->total_units.' unit</td>';
                echo '<td class="text-right">Rp '.number_format($pr->total_revenue, 0, ',', '.').'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            // Performa Petugas
            echo '<h3>3. Rekap Performa Petugas / Kasir</h3>';
            echo '<table>';
            echo '<thead><tr><th>Nama Petugas</th><th>Username</th><th>Jumlah Transaksi</th><th>Unit Terjual</th><th>Total Nominal Penjualan (Rp)</th></tr></thead><tbody>';
            foreach ($recap['cashiers_recap'] as $cr) {
                echo '<tr>';
                echo '<td class="font-bold">'.htmlspecialchars($cr->user_name).'</td>';
                echo '<td>@'.htmlspecialchars($cr->username).'</td>';
                echo '<td class="text-center">'.$cr->tx_count.'</td>';
                echo '<td class="text-center">'.$cr->total_units.'</td>';
                echo '<td class="text-right font-bold">Rp '.number_format($cr->total_revenue, 0, ',', '.').'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            echo '</body></html>';
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
