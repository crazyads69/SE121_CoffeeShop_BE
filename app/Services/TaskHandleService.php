<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class TaskHandleService
{
    use ResponseTrait;
    protected RevenueService $revenueService;
    protected ProductService $productService;
    protected CustomerService $customerService;
    protected UserService $userService;
    protected VoucherService $voucherService;
    protected LoyalService $loyalService;
    protected BankConfigService $bankConfigService;

    public function __construct(
        RevenueService $revenueService,
        ProductService $productService,
        CustomerService $customerService,
        UserService $userService,
        VoucherService $voucherService,
        LoyalService $loyalService,
        BankConfigService $bankConfigService
    ) {
        $this->revenueService = $revenueService;
        $this->productService = $productService;
        $this->customerService = $customerService;
        $this->userService = $userService;
        $this->voucherService = $voucherService;
        $this->loyalService = $loyalService;
        $this->bankConfigService = $bankConfigService;
    }
    public function handleTask($taskData)
    {
        $taskInfo = is_string($taskData) ? json_decode($taskData, true) : $taskData;
        return match ($taskInfo->task_type) {
            'REVENUE_ANALYSIS' => $this->handleRevenueAnalysis($taskInfo),
            'PRODUCT_MANAGEMENT' => $this->handleProductManagement($taskInfo),
            'CUSTOMER_MANAGEMENT' => $this->handleCustomerManagement($taskInfo),
            'LOYAL_MANAGEMENT' => $this->handleLoyalManagement($taskInfo),
            'VOUCHER_MANAGEMENT' => $this->handleVoucherManagement($taskInfo),
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

    private function handleProductManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->productService->getListProduct(),
            'get_by_category' => $this->productService->getListProduct($taskInfo->parameters->category),
            'get_by_id' => $this->productService->getProductByInfo($taskInfo->parameters->id ?? null),
            'get_by_name' => $this->productService->getProductByInfo(null, $taskInfo->parameters->name ?? null),
            'delete_by_id' => $this->productService->deleteProduct($taskInfo->parameters->id ?? null),
            'delete_by_name' => $this->productService->deleteProduct(null, $taskInfo->parameters->name ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleCustomerManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->customerService->getListCustomer(),
            'get_by_id' => $this->customerService->getCustomerByInfo($taskInfo->parameters->id ?? null),
            'get_by_name' => $this->customerService->getCustomerByInfo(null, $taskInfo->parameters->name ?? null),
            'get_by_phone' => $this->customerService->getCustomerByInfo(null, null, $taskInfo->parameters->phone ?? null),
            'delete_by_id' => $this->customerService->deleteCustomer($taskInfo->parameters->id ?? null),
            'delete_by_name' => $this->customerService->deleteCustomer(null, $taskInfo->parameters->name ?? null),
            'delete_by_phone' => $this->customerService->deleteCustomer(null, null, $taskInfo->parameters->phone ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleVoucherManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->voucherService->getListVoucher(),
            'get_by_id' => $this->voucherService->getVoucherByInfo($taskInfo->parameters->id ?? null),
            'get_by_code' => $this->voucherService->getVoucherByInfo(null, $taskInfo->parameters->code ?? null),
            'delete_by_id' => $this->voucherService->deleteVoucher($taskInfo->parameters->id ?? null),
            'delete_by_code' => $this->voucherService->deleteVoucher(null, $taskInfo->parameters->code ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleUserManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->userService->getListUser(),
            'get_by_id' => $this->userService->getUserByInfo($taskInfo->parameters->id ?? null),
            'get_by_name' => $this->userService->getUserByInfo(null, $taskInfo->parameters->name ?? null),
            'get_by_email' => $this->userService->getUserByInfo(null, null, $taskInfo->parameters->email ?? null),
            'delete_by_id' => $this->userService->deleteUser($taskInfo->parameters->id ?? null),
            'delete_by_name' => $this->userService->deleteUser(null, $taskInfo->parameters->name ?? null),
            'delete_by_email' => $this->userService->deleteUser(null, null, $taskInfo->parameters->email ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    private function handleBankConfigManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->bankConfigService->getListBankConfig(),
            'get_by_id' => $this->bankConfigService->getBankConfigByInfo($taskInfo->parameters->id ?? null),
            'get_by_bank_id' => $this->bankConfigService->getBankConfigByInfo(null, $taskInfo->parameters->bank_id ?? null),
            'get_by_bank_number' => $this->bankConfigService->getBankConfigByInfo(null, null, $taskInfo->parameters->bank_number ?? null),
            'get_by_bank_account_name' => $this->bankConfigService->getBankConfigByInfo(null, null, null, $taskInfo->parameters->bank_account_name ?? null),
            'delete_by_id' => $this->bankConfigService->deleteBankConfig($taskInfo->parameters->id ?? null),
            'delete_by_bank_id' => $this->bankConfigService->deleteBankConfig(null, $taskInfo->parameters->bank_id ?? null),
            'delete_by_bank_number' => $this->bankConfigService->deleteBankConfig(null, null, $taskInfo->parameters->bank_number ?? null),
            'delete_by_bank_account_name' => $this->bankConfigService->deleteBankConfig(null, null, null,  $taskInfo->parameters->bank_account_name ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }

    public function handleLoyalManagement($taskInfo): JsonResponse
    {
        return match ($taskInfo->action) {
            'get_list' => $this->loyalService->getListLoyal(),
            'get_by_id' => $this->loyalService->getLoyalByInfo($taskInfo->parameters->id ?? null),
            'get_by_name' => $this->loyalService->getLoyalByInfo(null, $taskInfo->parameters->name ?? null),
            'delete_by_id' => $this->loyalService->deleteLoyal($taskInfo->parameters->id ?? null),
            'delete_by_name' => $this->loyalService->deleteLoyal(null, $taskInfo->parameters->name ?? null),
            default => $this->errorResponse('Không thể xác định yêu cầu của bạn', 500),
        };
    }
}
