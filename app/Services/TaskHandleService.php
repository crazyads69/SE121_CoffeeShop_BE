<?php

namespace App\Services;

use App\Traits\ResponseTrait;

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
            default => [
                'status' => 'error',
                'message' => 'Không thể xác định yêu cầu của bạn'
            ],
        };
    }

    private function handleRevenueAnalysis($taskInfo)
    {
        return match ($taskInfo['action']) {
            'get_by_month' => $this->successResponse(
                $this->revenueService->getRevenueByTimeRange($taskInfo['time_range']),
                'Lấy dữ liệu doanh thu theo tháng thành công'
            ),
            default => [
                'status' => 'error',
                'message' => 'Không thể xác định yêu cầu của bạn'
            ],
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
