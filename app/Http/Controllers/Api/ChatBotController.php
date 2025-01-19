<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatBotNormalService;
use App\Services\NaturalLanguagePatternService;
use App\Services\NaturalLanguageService;
use App\Services\OpenAiService;
use App\Services\TaskHandleService;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    protected OpenAiService $openAIService;
    protected TaskHandleService $taskHandleService;

    protected NaturalLanguageService $nlpService;

    public function __construct(OpenAiService $openAIService, TaskHandleService $taskHandleService, NaturalLanguageService $nlpService)
    {
        $this->openAIService = $openAIService;
        $this->taskHandleService = $taskHandleService;
        $this->nlpService = $nlpService;
    }

    public function __invoke(Request $request) {
        $userMessage = $request->get("message");
        $response = $this->openAIService->classifyTask($userMessage);
        if(!$response->isSuccessful()) {
            $dataResponseTrain = $response;
        } else {
            $dataResponse = $response->getData()->data;
            $dataResponseTrain = $this->taskHandleService->handleTask($dataResponse);
        }

        return response()->json($response);


        $data = $this->openAIService->response($userMessage, $dataResponseTrain);
        return nl2br($data);
    }

    public function normal(Request $request) {
        $userMessage = $request->get("message");

        // Phân loại task
        $response = $this->nlpService->classifyTask($userMessage);

        if (!$response->isSuccessful()) {
            $dataResponseTrain = $response;
            $task_type = "ERROR";
        } else {
            $dataResponse = $response->getData()->data;
            $task_type = $dataResponse->task_type;
            $dataResponseTrain = $this->taskHandleService->handleTask($dataResponse);
        }

        // Format response
        $formattedResponse = $this->nlpService->formatResponse(
            $userMessage,
            $dataResponseTrain,
            $task_type
        );

        return response()->json($response);

        return nl2br($formattedResponse);
    }
}
