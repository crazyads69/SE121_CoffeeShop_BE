<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankConfigRequest;
use App\Models\BankConfig;
use App\Utils\RandomHelper;

class BankConfigController extends Controller
{
    public function getBankConfig() {
        $bankConfigs = BankConfig::all()->toArray();

        if (empty($bankConfigs)) {
            $bankConfig = BankConfig::create([
                'bank_id' => 'MB',
                'bank_number' => '970422XXXXXXX',
                'bank_account_name' => 'Shopee Coffee',
                'api_key' => 'XXX',
                ]);

            return response()->json($bankConfig);
        }

        return response()->json($bankConfigs[0]);
    }

    public function storeBankConfig(StoreBankConfigRequest $request) {
        $bankConfigs = BankConfig::all();

        if (empty($bankConfigs)) {
            $bankConfig = BankConfig::create($request->validated());
            return response()->json($bankConfig)->setStatusCode(201);
        } else {
            $bankConfigs[0]->update($request->validated());
            return response()->json($bankConfigs[0])->setStatusCode(201);
        }
    }

    public function testBankConfig() {
        $bankConfigs = BankConfig::all()->toArray();

        if (empty($bankConfigs)) {
            $bankConfig = BankConfig::create([
                'bank_id' => '970422',
                'bank_number' => '1234567890',
                'bank_account_name' => 'Shopee Coffee',
                'api_key' => 'XXX',
                ]);
        } else {
            $bankConfig = $bankConfigs[0];
        }

        $bankID = $bankConfig['bank_id'];
        $bankNumber = $bankConfig['bank_number'];
        $qrTemplate = 'compact2';
        $accountName = $bankConfig['bank_account_name'];
        $accountName = str_replace(' ', '%20', $accountName);

        $randomString = RandomHelper::generateRandomString(10);

        $price = "100000";
        $description = "$randomString$price";

        $template = "https://img.vietqr.io/image/$bankID-$bankNumber-$qrTemplate.png?amount=$price&addInfo=$description&accountName=$accountName";

        $data = [
            'qr' => $template,
        ];

        return response()->json($data);
    }
}
