<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenAI;
use OpenAI\Client;
use Yosymfony\Toml\Exception\ParseException;
use Yosymfony\Toml\Toml;

class OpenAiService
{
    use ResponseTrait;

    protected mixed $apiKey;
    protected Client $client;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->client = OpenAI::client($this->apiKey);
    }

    public function classifyTask($userMessage): JsonResponse
    {
        try {
            // Parse the TOML file
            $config = Toml::parseFile(resource_path('prompt/task_classifier.toml'));

            // Accessing the system prompt from the TOML structure
            $systemPrompt = $config['task_classifier']['system'];
            $userPromptTemplate = $config['task_classifier']['user'];

            // Replace placeholder {{ message }} in user prompt template with the actual message
            $userPrompt = str_replace('{{ message }}', $userMessage, $userPromptTemplate);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ];

            $responseAi = $this->client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => $messages,
            ]);

            $response = $this->decodeJson($responseAi['choices'][0]['message']['content']);
            if ($response === null) {
                return $this->errorResponse('Error parsing JSON response');
            }
            return $this->successResponse(
                $response,
                'Task classified successfully'
            );
        } catch (ParseException $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function response($question, $response)
    {
        $responsePrompt = $this->prepareResponsePrompt($question, $response);
        $messages = [
            ['role' => 'user', 'content' => $responsePrompt],
        ];
        $responseAi = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => $messages,
        ]);

        $dataResponse = $responseAi['choices'][0]['message']['content'];

        return trim(str_replace("```", "", $dataResponse));
    }

    protected function prepareResponsePrompt($question, $response)
    {
        $jsonEncode = json_encode($response);
        $promptTemplate =
            "Hãy trả lời câu hỏi dưới đây một cách chính xác và chuyên nghiệp:
            Câu hỏi của người dùng : {{question}}
            Dựa trên câu trả lời sau:{{information}}
            Yêu cầu khi trả lời:
            1. Phải dựa trực tiếp vào thông tin được cung cấp
            2. Nội dung phải quy đổi về dễ hiểu , dễ đọc , nếu data hãy quy đổi về dạng bảng
            3. Trả lời bằng tiếng Việt, rõ ràng và dễ hiểu
            4. Trả lời như chatbot với người dùng
            5. Trả lời phải chứa đầy đủ thông tin về câu trả lời
            ";

        $promptV1 = str_replace('{{question}}', $question, $promptTemplate);
        $promptV2 = str_replace('{{information}}', $jsonEncode, $promptV1);

        return $promptV2;
    }

    private function decodeJson($jsonContent)
    {
        $jsonContent = trim(str_replace("```json\n", "", $jsonContent));
        $jsonContent = trim(str_replace("```", "", $jsonContent));

        $dataArray = json_decode($jsonContent, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $dataArray;
        } else {
            return null;
        }
    }
}
