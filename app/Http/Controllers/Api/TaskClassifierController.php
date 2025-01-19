<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankConfigRequest;
use App\Models\BankConfig;
use App\Services\HandleResponseTask;
use App\Utils\RandomHelper;
use Exception;
use Illuminate\Http\Request;
use Yosymfony\Toml\Toml;

class TaskClassifierController extends Controller
{
    public function __invoke(Request $request) {
        try {

            // Parse the TOML file
            $config = Toml::parseFile(resource_path('prompt/task_classifier.toml'));

            // Accessing the system prompt from the TOML structure
            $systemPrompt = $config['task_classifier']['system'];
            $userPromptTemplate = $config['task_classifier']['user'];

            // Example user message
            $userMessage = $request->get("message");

            // Replace placeholder {{ message }} in user prompt template with the actual message
            $userPrompt = str_replace('{{ message }}', $userMessage, $userPromptTemplate);

            $apiKey = env('OPEN_API_KEY');
            $client = \OpenAI::client($apiKey);

// Prepare the messages payload
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ];

            try {
                // Send the chat request using the OpenAI client
                $response = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => $messages,
                ]);

            } catch (Exception $e) {
                // Handle errors
                echo "Error: " . $e->getMessage();
            }

            return response()->json()->setData(['task' => $userMessage, $response['choices'][0]['message']['content']]);

        } catch (\Yosymfony\Toml\Exception\ParseException $e) {
            echo "Error parsing TOML file: " . $e->getMessage();
        }
    }
}
