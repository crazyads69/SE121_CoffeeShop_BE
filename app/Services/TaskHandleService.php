<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class TaskHandleService
{
    use ResponseTrait;
    protected RevenueService $revenueService;
    public function __construct(
        RevenueService $revenueService
    ) {
        $this->revenueService = $revenueService;
    }
    public function handleTask($taskData)
    {
        $taskInfo = is_string($taskData) ? json_decode($taskData, true) : $taskData;
        return match ($taskInfo->task_type) {
            'REVENUE_ANALYSIS' => $this->handleRevenueAnalysis($taskInfo),
            'PRODUCT_MANAGEMENT' => $this->handleProductManagement($taskInfo),
            'CUSTOMER_MANAGEMENT' => $this->handleCustomerManagement($taskInfo),
            'VOUCHER_MANAGEMENT' => $this->handleVoucherManagement($taskInfo),
            'INVOICE_MANAGEMENT' => $this->handleInvoiceManagement($taskInfo),
            'USER_MANAGEMENT' => $this->handleUserManagement($taskInfo),
            'BANK_CONFIG_MANAGEMENT' => $this->handleBankConfigManagement($taskInfo),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleRevenueAnalysis($taskInfo): JsonResponse
    {
        $timeRangeActions = [
            'get_by_date',
            'get_by_month',
            'get_by_year',
            'get_by_date_range'
        ];

        if (in_array($taskInfo->action, $timeRangeActions)) {
            return $this->revenueService->getRevenueByTimeRange($taskInfo->time_range);
        }

        return match ($taskInfo->action) {
            'get_by_product' => $this->revenueService->highestRevenueProducts(),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleProductManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->productRepo->delete($taskInfo['parameters']['product_id']);
// Các action khác...
        }
    }

    private function handleCustomerManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->customerRepo->delete($taskInfo['parameters']['customer_id']);
// Các action khác...
        }
    }

    private function handleVoucherManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->voucherRepo->delete($taskInfo['parameters']['voucher_id']);
// Các action khác...
        }
    }

    private function handleInvoiceManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->invoiceRepo->delete($taskInfo['parameters']['invoice_id']);
// Các action khác...
        }
    }

    private function handleUserManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->userRepo->delete($taskInfo['parameters']['user_id']);
// Các action khác...
        }
    }

    private function handleBankConfigManagement($taskInfo)
    {
        switch ($taskInfo['action']) {
            case 'delete':
                return $this->bankConfigRepo->delete($taskInfo['parameters']['config_id']);
// Các action khác...
        }
    }

// Các method handle khác...
}
