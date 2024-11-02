<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankConfigRequest;
use App\Models\BankConfig;
use App\Utils\RandomHelper;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    public function __invoke(Request $request) {
        return response()->json("Hello, I'm ChatBot");
    }
}
