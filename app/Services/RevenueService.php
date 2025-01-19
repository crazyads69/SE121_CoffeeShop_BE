<?php

namespace App\Services;

use App\Models\Invoice;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    use ResponseTrait;

    public function getRevenueByTimeRange($timeRange): JsonResponse
    {
        $currentYear = date('Y');

        if (preg_match('/^\d{4}$/', $timeRange)) {
            // Month only (MM) - tự động thêm năm hiện tại
            $data = $this->getByYear($timeRange);
        } elseif (preg_match('/^\d{2}$/', $timeRange)) {
            // Month only (MM) - tự động thêm năm hiện tại
            $timeRange = sprintf('%s/%s', $timeRange, $currentYear);
            $data = $this->getByMonth($timeRange);
        } elseif (preg_match('/^\d{2}\/\d{4}$/', $timeRange)) {
            // Specific month (MM/YYYY)
            $data = $this->getByMonth($timeRange);
        } elseif (preg_match('/^\d{2}\/\d{4}-\d{2}\/\d{4}$/', $timeRange)) {
            // Date range (MM/YYYY-MM/YYYY)
            list($startDate, $endDate) = explode('-', $timeRange);
            $data = $this->getByMonthRange($startDate, $endDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}-\d{2}\/\d{2}\/\d{4}$/', $timeRange)) {
            // Date range (MM/YYYY-MM/YYYY)
            list($startDate, $endDate) = explode('-', $timeRange);
            $data = $this->getByDateRange($startDate, $endDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $timeRange)) {
            // Specific date (DD/MM/YYYY)
            $data = $this->getByDate($timeRange);
        } else {
            return $this->errorResponse('Lỗi Định Dạng Ngày, Vui Lòng Thử Lại !!!');
        }

        return $this->successResponseAnalytics($data);
    }

    private function getInfoDataInvoices($invoices): array
    {
        $result = [];
        foreach ($invoices as $invoice) {
            $invoiceData = [
                'customer_name' => isset($invoice->customer) ? $invoice->customer->name : 'Không có tên admin xử lý',
                'user_name' => isset($invoice->user) ? $invoice->user->name : 'Không có tên khách hàng',
                'total_price' => $invoice->final_price,
                'created_at' => $invoice->created_at,
                'products' => []
            ];

            foreach ($invoice->invoiceDetails as $detail) {
                $invoiceData['products'][] = [
                    'product_name' => isset($detail->products) ? $detail->products->name : 'Không có tên sản phẩm',
                    'quantity' => $detail->quantity
                ];
            }

            $result[] = $invoiceData;
        }

        return $result;
    }

    private function getByMonth($timeRange): array
    {
        list($month, $year) = explode('/', $timeRange);
        $invoices = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with(['invoiceDetails.products', 'customer', 'user'])
            ->get();
        return $this->getInfoDataInvoices($invoices);
    }

    private function getByMonthRange($startDate, $endDate): JsonResponse
    {
        $startDate = Carbon::createFromFormat('m/Y', $startDate);
        $endDate = Carbon::createFromFormat('m/Y', $endDate)->endOfMonth();
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with(['invoiceDetails.products', 'customer', 'user'])->get();
        $result = $this->getInfoDataInvoices($invoices);
        return response()->json($result);
    }

    private function getByDateRange($startDate, $endDate): JsonResponse
    {
        $startDate = Carbon::createFromFormat('d/m/Y', $startDate);
        $endDate = Carbon::createFromFormat('d/m/Y', $endDate);
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with(['invoiceDetails.products', 'customer', 'user'])->get();
        $result = $this->getInfoDataInvoices($invoices);
        return response()->json($result);
    }

    private function getByDate($date): JsonResponse
    {
        $date = Carbon::createFromFormat('d/m/Y', $date);
        $invoices = Invoice::whereDate('created_at', $date)
            ->with(['invoiceDetails.products', 'customer', 'user'])->get();
        $result = $this->getInfoDataInvoices($invoices);
        return response()->json($result);
    }

    private function getByYear($timeRange)
    {
        $invoices = Invoice::whereYear('created_at', $timeRange)
            ->with(['invoiceDetails.products', 'customer', 'user'])->get();
        $result = $this->getInfoDataInvoices($invoices);
        return response()->json($result);
    }

    public function highestRevenueProducts()
    {
        $highestRevenueProduct = DB::table('invoice_detail')
            ->select('product_id', 'product.name',DB::raw('SUM(quantity * invoice_detail.unit_price) as total_revenue'))
            ->join('product', 'invoice_detail.product_id', '=', 'product.id')
            ->join('invoice', 'invoice_detail.invoice_id', '=', 'invoice.id')
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->take(5)->get();

        if ($highestRevenueProduct->isEmpty()) {
            return $this->errorResponse('Không có dữ liệu !!!');
        }
        return $this->successResponseAnalytics($highestRevenueProduct);
    }

    public function successResponseAnalytics($data) {
        return $this->successResponse($data, 'Lấy Dữ Liệu Thành Công !!!', true);
    }
}
