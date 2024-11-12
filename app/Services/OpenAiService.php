<?php

namespace App\Services;

use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenAI;
use Yosymfony\Toml\Exception\ParseException;
use Yosymfony\Toml\Toml;

class OpenAiService
{
    use ResponseTrait;
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

            $apiKey = config('services.openai.api_key');
            $client = OpenAI::client($apiKey);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ];

            $responseAi = $client->chat()->create([
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
