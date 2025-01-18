<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ChatBotNormalService
{
    use ResponseTrait;

    private array $keywords = [
        'REVENUE_ANALYSIS' => [
            'patterns' => [
                'doanh thu' => [
                    'theo ngày' => ['action' => 'get_by_date', 'params' => ['time_range']],
                    'theo tháng' => ['action' => 'get_by_month', 'params' => ['time_range']],
                    'theo năm' => ['action' => 'get_by_year', 'params' => ['time_range']],
                    'hôm nay' => ['action' => 'get_by_current_day'],
                    'hôm qua' => ['action' => 'get_by_yesterday'],
                    'ngày mai' => ['action' => 'get_by_tomorrow'],
                    'sản phẩm' => ['action' => 'get_by_product']
                ]
            ]
        ],
        'PRODUCT_MANAGEMENT' => [
            'patterns' => [
                'sản phẩm' => [
                    'danh sách' => ['action' => 'get_list'],
                    'thông tin' => ['action' => 'get_by_id', 'params' => ['id']],
                    'tìm theo tên' => ['action' => 'get_by_name', 'params' => ['name']],
                    'xóa' => ['action' => 'delete_by_id', 'params' => ['id']]
                ]
            ]
        ],
        // ... Các pattern khác giữ nguyên
    ];

    protected TaskHandleService $taskHandleService;

    public function __construct(TaskHandleService $taskHandleService)
    {
        $this->taskHandleService = $taskHandleService;
    }

    public function classifyTask($userMessage): JsonResponse
    {
        try {
            $taskInfo = $this->analyzeMessage($userMessage);

            if (!$taskInfo) {
                return $this->errorResponse('Không thể xác định yêu cầu');
            }

            return $this->successResponse($taskInfo, 'Task classified successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function handleTask($taskData)
    {
        return $this->taskHandleService->handleTask($taskData);
    }

    public function response($question, $response)
    {
        $isAnalytics = $response->getData()->isAnalytics ?? false;
        return $this->formatResponse($question, $response, $isAnalytics);
    }

    protected function analyzeMessage($message): ?array
    {
        $message = mb_strtolower($message);

        foreach ($this->keywords as $taskType => $config) {
            foreach ($config['patterns'] as $mainKeyword => $actions) {
                if (str_contains($message, $mainKeyword)) {
                    foreach ($actions as $actionKeyword => $actionConfig) {
                        if (str_contains($message, $actionKeyword)) {
                            $parameters = [];
                            if (isset($actionConfig['params'])) {
                                foreach ($actionConfig['params'] as $param) {
                                    $parameters[$param] = $this->extractParameter($message, $param);
                                }
                            }

                            return [
                                'task_type' => $taskType,
                                'action' => $actionConfig['action'],
                                'parameters' => $parameters
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    protected function extractParameter($message, $paramType): ?string
    {
        $patterns = [
            'id' => '/id[:\s]*(\d+)/',
            'name' => '/tên[:\s]*"([^"]+)"/',
            'phone' => '/số điện thoại[:\s]*"([^"]+)"/',
            'email' => '/email[:\s]*"([^"]+)"/',
            'code' => '/mã[:\s]*"([^"]+)"/',
            'time_range' => '/(\d{1,2}\/\d{1,2}\/\d{4})/'
        ];

        if (isset($patterns[$paramType])) {
            if (preg_match($patterns[$paramType], $message, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function formatResponse($question, $response, $isAnalytics): string
    {
        $data = json_decode(json_encode($response), true);

        $formattedResponse = "Xin chào! Dựa trên yêu cầu của bạn, đây là thông tin chi tiết:\n\n";

        if (isset($data['data'])) {
            $formattedResponse .= $this->formatDataToTable($data['data']);
        }

        if ($isAnalytics) {
            $formattedResponse .= "\n\nPhân tích:\n";
            $formattedResponse .= $this->generateAnalytics($data);
        }

        $formattedResponse .= "\n\nHy vọng thông tin sẽ hữu ích tới bạn! Tôi có thể giúp gì thêm cho bạn?";

        return $formattedResponse;
    }

    protected function formatDataToTable($data): string
    {
        if (empty($data)) {
            return "Không có dữ liệu.";
        }

        $output = "";
        if (is_array($data)) {
            if (is_array(reset($data))) {
                $headers = array_keys(reset($data));
                $output .= implode("\t|\t", $headers) . "\n";
                $output .= str_repeat("-", 100) . "\n";

                foreach ($data as $row) {
                    $output .= implode("\t|\t", array_values($row)) . "\n";
                }
            } else {
                foreach ($data as $key => $value) {
                    $output .= "$key: $value\n";
                }
            }
        } else {
            $output .= $data;
        }

        return $output;
    }

    protected function generateAnalytics($data): string
    {
        $analysis = "";

        if (isset($data['data'])) {
            if (isset($data['data']['revenue'])) {
                $revenue = $data['data']['revenue'];
                $analysis .= "- Tổng doanh thu: " . number_format($revenue, 0, ',', '.') . " VNĐ\n";

                if (isset($data['data']['previous_revenue'])) {
                    $previousRevenue = $data['data']['previous_revenue'];
                    $percentChange = (($revenue - $previousRevenue) / $previousRevenue) * 100;
                    $trend = $percentChange >= 0 ? "tăng" : "giảm";
                    $analysis .= "- So với kỳ trước: $trend " . abs(round($percentChange, 2)) . "%\n";
                }
            }

            if (isset($data['data']['top_products'])) {
                $analysis .= "\nTop sản phẩm bán chạy:\n";
                foreach ($data['data']['top_products'] as $product) {
                    $analysis .= "- {$product['name']}: {$product['quantity']} sản phẩm\n";
                }
            }
        }

        return $analysis;
    }
}
