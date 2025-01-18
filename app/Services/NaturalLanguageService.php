<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NaturalLanguageService
{
    use ResponseTrait;

    private array $taskDefinitions = [
        'REVENUE_ANALYSIS' => [
            'mainTable' => 'invoice',
            'relatedTables' => ['invoice_detail'],
            'keywords' => ['doanh thu', 'thu nhập', 'lợi nhuận', 'báo cáo', 'doanh số', 'hóa đơn', 'bill', 'đơn hàng', 'biên lai'],
            'actions' => [
                'get_by_only_day' => ['ngày này', 'ngày hiện tại'],
                'get_by_current_day' => ['hôm nay', 'ngày hôm nay'],
                'get_by_yesterday' => ['hôm qua', 'ngày hôm qua'],
                'get_by_tomorrow' => ['ngày mai', 'hôm sau'],
                'get_by_date' => ['theo ngày', 'ngày'],
                'get_by_month' => ['theo tháng', 'tháng'],
                'get_by_year' => ['theo năm', 'năm'],
                'get_by_date_range' => ['từ ngày', 'khoảng thời gian'],
                'get_by_product' => ['theo sản phẩm', 'từng sản phẩm']
            ]
        ],
        'PRODUCT_MANAGEMENT' => [
            'mainTable' => 'product',
            'relatedTables' => [],
            'keywords' => ['sản phẩm', 'đồ uống', 'món', 'mặt hàng', 'giá', 'tồn kho', 'số lượng'],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_category' => ['loại', 'danh mục'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_name' => ['tên', 'tìm theo tên'],
                'delete_by_id' => ['xóa mã', 'xóa id'],
                'delete_by_name' => ['xóa tên']
            ],
            'categories' => ['Đồ ăn', 'Đồ uống']
        ],
        'CUSTOMER_MANAGEMENT' => [
            'mainTable' => 'customer',
            'relatedTables' => [],
            'keywords' => ['khách hàng', 'khách', 'member', 'thành viên'],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_name' => ['tên', 'tìm theo tên'],
                'get_by_phone' => ['số điện thoại', 'sđt', 'phone'],
                'delete_by_id' => ['xóa mã', 'xóa id'],
                'delete_by_name' => ['xóa tên'],
                'delete_by_phone' => ['xóa số điện thoại']
            ]
        ],
        'VOUCHER_MANAGEMENT' => [
            'mainTable' => 'voucher',
            'relatedTables' => [],
            'keywords' => ['voucher', 'mã giảm giá', 'khuyến mãi', 'ưu đãi'],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_code' => ['code', 'mã'],
                'delete_by_id' => ['xóa mã số', 'xóa id'],
                'delete_by_code' => ['xóa code', 'xóa mã']
            ]
        ],
        'USER_MANAGEMENT' => [
            'mainTable' => 'user',
            'relatedTables' => [],
            'keywords' => ['nhân viên', 'người dùng', 'tài khoản', 'user'],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_name' => ['tên', 'tìm theo tên'],
                'get_by_email' => ['email', 'mail'],
                'delete_by_id' => ['xóa mã', 'xóa id'],
                'delete_by_name' => ['xóa tên'],
                'delete_by_email' => ['xóa email']
            ]
        ],
        'BANK_CONFIG_MANAGEMENT' => [
            'mainTable' => 'bank_config',
            'relatedTables' => [],
            'keywords' => ['ngân hàng', 'cấu hình', 'thanh toán', 'cấu hình ngân hàng', 'bank'],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_bank_id' => ['mã ngân hàng', 'bank id'],
                'get_by_bank_number' => ['số tài khoản', 'stk'],
                'get_by_bank_account_name' => ['tên tài khoản', 'chủ tài khoản'],
                'delete_by_id' => ['xóa mã', 'xóa id'],
                'delete_by_bank_id' => ['xóa mã ngân hàng'],
                'delete_by_bank_number' => ['xóa số tài khoản'],
                'delete_by_bank_account_name' => ['xóa tên tài khoản']
            ]
        ],
        'LOYAL_MANAGEMENT' => [
            'mainTable' => 'loyal',
            'relatedTables' => [],
            'keywords' => [
                'loyal', 'thân thiết', 'quen',
                'thường xuyên', 'vip', 'trung thành'
            ],
            'actions' => [
                'get_list' => ['danh sách', 'tất cả'],
                'get_by_id' => ['mã số', 'id'],
                'get_by_name' => ['tên', 'tìm theo tên'],
                'delete_by_id' => ['xóa mã', 'xóa id'],
                'delete_by_name' => ['xóa tên']
            ]
        ]
    ];

    public function classifyTask(string $message): JsonResponse
    {
        $message = mb_strtolower($message);

        $result = [
            'task_type' => 'UNKNOWN',
            'tables' => [],
            'action' => '',
            'time_range' => '',
            'parameters' => []
        ];

        // Xác định task type và tables
        foreach ($this->taskDefinitions as $type => $definition) {
            if ($this->containsAnyKeyword($message, $definition['keywords'])) {
                $result['task_type'] = $type;
                $result['tables'] = array_merge([$definition['mainTable']], $definition['relatedTables']);
                break;
            }
        }

        if ($result['task_type'] !== 'UNKNOWN') {
            // Xác định action
            $taskDef = $this->taskDefinitions[$result['task_type']];
            $result['action'] = $this->identifyAction($message, $taskDef['actions']);

            // Xử lý time range cho REVENUE_ANALYSIS
            if ($result['task_type'] === 'REVENUE_ANALYSIS') {
                $result['time_range'] = $this->extractTimeRange($message);
            }

            // Xử lý parameters
            $result['parameters'] = $this->extractParameters($message, $result['task_type']);
        }

        return $this->successResponse($result, 'Task classified successfully');
    }

    private function containsAnyKeyword(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function identifyAction(string $message, array $actions): string
    {
        foreach ($actions as $action => $keywords) {
            if ($this->containsAnyKeyword($message, $keywords)) {
                return $action;
            }
        }
        return 'get_list'; // Action mặc định
    }

    private function extractTimeRange(string $message): string
    {
        $currentYear = date('Y');

        // Xử lý năm
        if (preg_match('/năm (\d{4})/', $message, $matches)) {
            return $matches[1];
        }

        // Xử lý tháng/năm
        if (preg_match('/tháng (\d{1,2})\/(\d{4})/', $message, $matches)) {
            return sprintf('%02d/%s', $matches[1], $matches[2]);
        }

        // Xử lý chỉ tháng
        if (preg_match('/tháng (\d{1,2})/', $message, $matches)) {
            return sprintf('%02d/%s', $matches[1], $currentYear);
        }

        // Xử lý ngày/tháng/năm
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $message, $matches)) {
            return sprintf('%02d/%02d/%04d', $matches[1], $matches[2], $matches[3]);
        }

        // Xử lý khoảng thời gian
        if (preg_match('/từ (\d{1,2})\/(\d{1,2})\/(\d{4}) đến (\d{1,2})\/(\d{1,2})\/(\d{4})/', $message, $matches)) {
            return sprintf('%02d/%02d/%04d-%02d/%02d/%04d',
                $matches[1], $matches[2], $matches[3],
                $matches[4], $matches[5], $matches[6]
            );
        }

        return '';
    }

    private function extractParameters(string $message, string $taskType): array
    {
        $parameters = [];

        switch ($taskType) {
            case 'PRODUCT_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/tên[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['name'] = $matches[1];
                }
                if (str_contains($message, 'đồ ăn')) {
                    $parameters['category'] = 'Đồ ăn';
                } elseif (str_contains($message, 'đồ uống')) {
                    $parameters['category'] = 'Đồ uống';
                }
                break;

            case 'CUSTOMER_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/tên[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['name'] = $matches[1];
                }
                if (preg_match('/số điện thoại[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['phone'] = $matches[1];
                }
                break;

            case 'VOUCHER_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/mã[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['code'] = $matches[1];
                }
                break;

            case 'USER_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/tên[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['name'] = $matches[1];
                }
                if (preg_match('/email[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['email'] = $matches[1];
                }
                break;

            case 'BANK_CONFIG_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/mã ngân hàng[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['bank_id'] = $matches[1];
                }
                if (preg_match('/số tài khoản[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['bank_number'] = $matches[1];
                }
                if (preg_match('/tên tài khoản[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['bank_account_name'] = $matches[1];
                }
                break;

            case 'LOYAL_MANAGEMENT':
                if (preg_match('/id[:\s]*(\d+)/', $message, $matches)) {
                    $parameters['id'] = $matches[1];
                }
                if (preg_match('/tên[:\s]*"([^"]+)"/', $message, $matches)) {
                    $parameters['name'] = $matches[1];
                }
                break;
        }

        return $parameters;
    }

    public function formatResponse(string $userMessage, JsonResponse $response, $task_type = "Error"): string
    {
        // Get decoded data directly
        $responseData = $response->getData();

        if (!$responseData->success) {
            return "Có lỗi xảy ra khi lấy dữ liệu.";
        }

        $task_type = strtolower($task_type);

        if ($task_type == "error") {
            return "Không thể xác định loại công việc cần thực hiện.";
        }

        $data = $responseData->data;
//        'REVENUE_ANALYSIS' => $this->handleRevenueAnalysis($taskInfo),
//            'PRODUCT_MANAGEMENT' => $this->handleProductManagement($taskInfo),
//            'CUSTOMER_MANAGEMENT' => $this->handleCustomerManagement($taskInfo),
//            'LOYAL_MANAGEMENT' => $this->handleLoyalManagement($taskInfo),
//            'VOUCHER_MANAGEMENT' => $this->handleVoucherManagement($taskInfo),
//            'USER_MANAGEMENT' => $this->handleUserManagement($taskInfo),
//            'BANK_CONFIG_MANAGEMENT' => $this->handleBankConfigManagement($taskInfo),

        if ($task_type == "product_management") {
            return $this->formatProductResponse($userMessage, $data);
        } else if ($task_type == "revenue_analysis") {
            return $this->formatAnalyticsResponse($userMessage, $data->original);
        } else if ($task_type == "customer_management") {
            return $this->formatCustomerResponse($userMessage, $data);
        } else if ($task_type == "loyal_management") {
            return $this->formatCustomerLoyalResponse($userMessage, $data);
        } else if ($task_type == "voucher_management") {
            return $this->formatVouchersResponse($userMessage, $data);
        } else if ($task_type == "user_management") {
            return $this->formatUsersResponse($userMessage, $data);
        } else if ($task_type == "bank_config_management") {
            return $this->formatBanksResponse($userMessage, $data);
        }

        return "Không thể xác định định dạng dữ liệu phù hợp.";
    }

    private function formatAnalyticsResponse(string $userMessage, $orders): string
    {
        $table = "| STT | Thời gian | Khách hàng | Thu ngân | Sản phẩm | Số lượng | Tổng tiền |\n";
        $table .= "|-----|-----------|------------|-----------|-----------|-----------|------------|\n";

        if (isset($orders->id)) {
            $table .= sprintf("| %d | %s | %s | %s | %s | %d | %s |\n",
                '1',
                date('d/m/Y', strtotime($orders->created_at)),
                $orders->customer_name,
                $orders->user_name,
                implode(", ", $orders),
                count($orders->products),
                number_format($orders->total_price, 0, ',', '.') . 'đ'
            );
        } else {
            $index = 1;
            foreach ($orders as $order) {
                $products = [];
                foreach ($order->products as $product) {
                    $products[] = sprintf("%s (x%d)", $product->product_name, $product->quantity);
                }

                $table .= sprintf("| %d | %s | %s | %s | %s | %d | %s |\n",
                    $index++,
                    date('d/m/Y', strtotime($order->created_at)),
                    $order->customer_name,
                    $order->user_name,
                    implode(", ", $products),
                    count($order->products),
                    number_format($order->total_price, 0, ',', '.') . 'đ'
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatProductResponse(string $userMessage, $products): string
    {
        $table = "| STT | Tên sản phẩm | Hình ảnh | Đơn giá | Loại | Ngày tạo | Cập nhật |\n";
        $table .= "|-----|-----------|------------|-----------|-----------|-----------|------------|\n";

        if (isset($products->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s | %s | %s |\n",
                '1',
                $products->name,
                $products->image,
                $products->unit_price,
                $products->type,
                date('d/m/Y', strtotime($products->created_at)),
                date('d/m/Y', strtotime($products->updated_at)),
            );
        } else {
            $index = 1;
            foreach ($products as $product) {
                $table .= sprintf("| %d | %s | %s | %s | %s | %s | %s |\n",
                    $index++,
                    $product->name,
                    $product->image,
                    $product->unit_price,
                    $product->type,
                    date('d/m/Y', strtotime($product->created_at)),
                    date('d/m/Y', strtotime($product->updated_at)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatCustomerResponse(string $userMessage, $customers): string
    {
        $table = "| STT | Tên khách hàng | Số điện thoại | Ngày tạo | Cập nhật |\n";
        $table .= "|-----|-----------|------------|-----------|-----------|-----------|------------|\n";

        if (isset($customers->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s |\n",
                '1',
                $customers->name,
                $customers->phone_number,
                date('d/m/Y', strtotime($customers->created_at)),
                date('d/m/Y', strtotime($customers->updated_at)),
            );
        } else {
            $index = 1;
            foreach ($customers as $customer) {
                $table .= sprintf("%s | %s | %s | %s | %s |\n",
                    $index++,
                    $customer->name,
                    $customer->phone_number,
                    date('d/m/Y', strtotime($customer->created_at)),
                    date('d/m/Y', strtotime($customer->updated_at)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatCustomerLoyalResponse(string $userMessage, $customers): string
    {
        $table = "| STT | Tên khách hàng | Loại | Khoảng tiền | Ngày tạo | Cập nhật |\n";
        $table .= "|-----|-----------|------------|------------|-----------|-----------|------------|\n";

        if (isset($customers->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s | %s |\n",
                '1',
                $customers->name,
                $customers->type,
                $customers->amount,
                date('d/m/Y', strtotime($customers->created_at)),
                date('d/m/Y', strtotime($customers->updated_at)),
            );
        } else {
            $index = 1;
            foreach ($customers as $customer) {
                $table .= sprintf("%s | %s | %s | %s | %s | %s |\n",
                    $index++,
                    $customer->name,
                    $customer->type,
                    $customer->amount,
                    date('d/m/Y', strtotime($customer->created_at)),
                    date('d/m/Y', strtotime($customer->updated_at)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatVouchersResponse(string $userMessage, $vouchers): string
    {
        $table = "| STT | Voucher Code | Loại | Giá trị | Số lượng | Ngày bắt đầu | Ngày hết hạn |\n";
        $table .= "|-----|-----------|------------|------------|-----------|-----------|------------|\n";

        if (isset($voucher->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s | %s | %s |\n",
                '1',
                $vouchers->voucher_code,
                $vouchers->type,
                $vouchers->amount,
                $vouchers->quantity,
                date('d/m/Y', strtotime($vouchers->start_date)),
                date('d/m/Y', strtotime($vouchers->end_date)),
            );
        } else {
            $index = 1;
            foreach ($vouchers as $voucher) {
                $table .= sprintf("%s | %s | %s | %s | %s | %s | %s |\n",
                    $index++,
                    $voucher->voucher_code,
                    $voucher->type,
                    $voucher->amount,
                    $voucher->quantity,
                    date('d/m/Y', strtotime($voucher->start_date)),
                    date('d/m/Y', strtotime($voucher->end_date)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatUsersResponse(string $userMessage, $users): string
    {
        $table = "| STT | Name | Email | Xác thực lúc | Vai trò | Ngày tạo | Ngày kết thúc |\n";
        $table .= "|-----|-----------|------------|------------|-----------|-----------|------------|\n";

        if (isset($users->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s | %s | %s |\n",
                '1',
                $users->name,
                $users->email,
                $users->email_verified_at,
                $users->role == 1 ? 'Admin' : 'Nhân viên',
                date('d/m/Y', strtotime($users->created_at)),
                date('d/m/Y', strtotime($users->updated_at)),
            );
        } else {
            $index = 1;
            foreach ($users as $user) {
                $table .= sprintf("%s | %s | %s | %s | %s | %s | %s |\n",
                    $index++,
                    $user->name,
                    $user->email,
                    $user->email_verified_at,
                    $user->role == 1 ? 'Admin' : 'Nhân viên',
                    date('d/m/Y', strtotime($user->created_at)),
                    date('d/m/Y', strtotime($user->updated_at)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }

    private function formatBanksResponse(string $userMessage, $banks): string
    {
        $table = "| STT | Id ngân hàng | Tài khoản ngân hàng | Tên ngân hàng |  Ngày tạo | Ngày kết thúc |\n";
        $table .= "|-----|-----------|------------|------------|-----------|-----------|------------|\n";

        if (isset($users->id)) {
            $table .= sprintf("%s | %s | %s | %s | %s | | %s |\n",
                '1',
                $banks->bank_id,
                $banks->bank_number,
                $banks->bank_account_name,
                date('d/m/Y', strtotime($banks->created_at)),
                date('d/m/Y', strtotime($banks->updated_at)),
            );
        } else {
            $index = 1;
            foreach ($banks as $bank) {
                $table .= sprintf("%s | %s | %s | %s | %s | | %s |\n",
                    $index++,
                    $bank->bank_id,
                    $bank->bank_number,
                    $bank->bank_account_name,
                    date('d/m/Y', strtotime($bank->created_at)),
                    date('d/m/Y', strtotime($bank->updated_at)),
                );
            }
        }

        return sprintf(
            "Dựa trên câu hỏi: %s\n\nDữ liệu chi tiết:\n%s\n\nHy vọng thông tin trên hữu ích! Tôi có thể giúp gì thêm cho bạn?",
            $userMessage,
            $table
        );
    }
}
