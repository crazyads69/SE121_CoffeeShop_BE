<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAiService;
use App\Services\TaskHandleService;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    protected OpenAiService $openAIService;
    protected TaskHandleService $taskHandleService;

    public function __construct(OpenAiService $openAIService, TaskHandleService $taskHandleService)
    {
        $this->openAIService = $openAIService;
        $this->taskHandleService = $taskHandleService;
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

        $data = $this->openAIService->response($userMessage, $dataResponseTrain);
        return nl2br($data);
    }
}
