<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankConfigRequest;
use App\Models\BankConfig;
use App\Utils\RandomHelper;
use Exception;
use Illuminate\Http\Request;
use Yosymfony\Toml\Toml;

class ChatBotController extends Controller
{
    public function __invoke(Request $request) {
        try {
            // Parse the TOML file
            $config = Toml::parseFile(resource_path('prompt/task_classifier.toml'));

            // Accessing the system prompt from the TOML structure
            $systemPrompt = $config['task_classifier']['system'];
            $userPromptTemplate = $config['task_classifier']['user'];

            // Example user message
            $userMessage = "Cho tôi danh sách nhân viên của cửa hàng";

            // Replace placeholder {{ message }} in user prompt template with the actual message
            $userPrompt = str_replace('{{ message }}', $userMessage, $userPromptTemplate);

            // Display the prompts
            echo "System Prompt:\n" . $systemPrompt . "\n";
            echo "\nUser Prompt:\n" . $userPrompt . "\n";

            // If you plan to use this in an API request, you can prepare the data like this:
            $apiRequest = [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ];

            // Print the API request payload to see the structure
            print_r($apiRequest);

        } catch (\Yosymfony\Toml\Exception\ParseException $e) {
            echo "Error parsing TOML file: " . $e->getMessage();
        }


        $apiKey = 'YOUR_OPENAI_API_KEY';
        $client = OpenAI::client($apiKey);

// Define the system and user prompts
        $systemPrompt = "You are an intelligent Chatbot Assistant, and your mission is task classification from a single user request...";
        $userPrompt = "Can you review my pull request on GitHub?";

// Prepare the messages payload
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        try {
            // Send the chat request using the OpenAI client
            $response = $client->chat()->create([
                'model' => 'gpt-4',
                'messages' => $messages,
            ]);

            // Output the response
            if (isset($response['choices'][0]['message']['content'])) {
                echo "ChatGPT response: " . $response['choices'][0]['message']['content'];
            } else {
                echo "Unexpected API response format:\n";
                print_r($response);
            }

        } catch (Exception $e) {
            // Handle errors
            echo "Error: " . $e->getMessage();
        }

        return response()->json("Hello, I'm ChatBot");
    }
}
