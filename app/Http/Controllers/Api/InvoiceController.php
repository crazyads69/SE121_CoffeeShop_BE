<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckBankRequest;
use App\Http\Requests\CheckoutInvoiceRequest;
use App\Http\Requests\MultipleDestroyRequest;
use App\Models\BankConfig;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\CartService;
use App\Services\CustomerService;
use App\Services\LoyalService;
use App\Services\VoucherService;
use App\Utils\RandomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('invoiceDetails', 'customer', 'staff', 'voucher')->paginate();

        return response()->json($invoices);
    }

    public function getTotalCart(CheckoutInvoiceRequest $request)
    {
        $cart = $request->input('cart');
        $totalPrice = CartService::calculateCart($cart);

        $voucherCode = $request->input('voucher_code');
        $customerPhoneNumber = $request->input('customer_phone_number');

        if ($voucherCode) {
            [
                'isAvailable' => $isVoucherAvailable,
                'voucherType' => $voucherType,
                'voucherAmount' => $voucherAmount,
                'quantity' => $quantity,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ] = VoucherService::verifyVoucher($voucherCode);

            $today = date('Y-m-d H:i:s');

            if (!$isVoucherAvailable || $quantity < 1 || $today > $endDate || $today < $startDate) {
                $data = [
                    'isSuccess' => false,
                    'message' => 'Voucher không hợp lệ hoặc đã hết hạn sử dụng, vui lòng xoá hoặc kiểm tra lại',
                ];

                return response($data, 200);
            }

            [$_, $finalPrice] = CartService::applyVoucher($totalPrice, $voucherType, $voucherAmount);

            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$_, $finalPriceLoyal] = LoyalService::applyLoyal($finalPrice, $loyal);

                $data = [
                    'total_price' => $finalPriceLoyal,
                ];

                return response($data, 200);
            } else {
                $data = [
                    'total_price' => $finalPrice,
                ];

                return response($data, 200);
            }
        } else {
            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$_, $finalPriceLoyal] = LoyalService::applyLoyal($totalPrice, $loyal);

                $data = [
                    'total_price' => $finalPriceLoyal,
                ];

                return response($data, 200);
            }
        }

        $data = [
            'total_price' => $totalPrice,
        ];

        return response($data, 200);
    }

    public function getQR(CheckoutInvoiceRequest $request)
    {
        $cart = $request->input('cart');
        $totalPrice = CartService::calculateCart($cart);

        $voucherCode = $request->input('voucher_code');
        $customerPhoneNumber = $request->input('customer_phone_number');


        $qrFinalPrice = $totalPrice;

        if ($voucherCode) {
            [
                'isAvailable' => $isVoucherAvailable,
                'voucherType' => $voucherType,
                'voucherAmount' => $voucherAmount,
                'quantity' => $quantity,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ] = VoucherService::verifyVoucher($voucherCode);

            $today = date('Y-m-d H:i:s');

            if (!$isVoucherAvailable || $quantity < 1 || $today > $endDate || $today < $startDate) {
                $data = [
                    'isSuccess' => false,
                    'message' => 'Voucher không hợp lệ hoặc đã hết hạn sử dụng, vui lòng xoá hoặc kiểm tra lại',
                ];

                return response($data, 200);
            }

            [$_, $finalPrice] = CartService::applyVoucher($totalPrice, $voucherType, $voucherAmount);

            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$_, $finalPriceLoyal] = LoyalService::applyLoyal($finalPrice, $loyal);

                $qrFinalPrice = $finalPriceLoyal;
            } else {

                $qrFinalPrice = $finalPrice;
            }
        } else {
            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$_, $finalPriceLoyal] = LoyalService::applyLoyal($totalPrice, $loyal);

                $qrFinalPrice = $finalPriceLoyal;
            }
        }

        $bankConfigs = BankConfig::all()->toArray();

        if (empty($bankConfigs)) {
            return response()->json(['error' => 'Bank config not found'])->setStatusCode(400);
        }

        $bankConfig = $bankConfigs[0];

        $bankID = $bankConfig['bank_id'];
        $bankNumber = $bankConfig['bank_number'];
        $qrTemplate = 'compact2';
        $accountName = $bankConfig['bank_account_name'];
        $accountName = str_replace(' ', '%20', $accountName);

        $randomString = RandomHelper::generateRandomString(10);
        $description = "$randomString$qrFinalPrice";

        $template = "https://img.vietqr.io/image/$bankID-$bankNumber-$qrTemplate.png?amount=$qrFinalPrice&addInfo=$description&accountName=$accountName";

        $data = [
            'qr' => $template,
            'random_code' => $randomString,
            'amount' => $qrFinalPrice,
        ];

        return response($data, 200);
    }

    public function checkBank(CheckBankRequest $request)
    {
        $bankConfigs = BankConfig::all()->toArray();

        if (empty($bankConfigs)) {
            return response()->json(['error' => 'Bank config not found'])->setStatusCode(400);
        }

        $bankConfig = $bankConfigs[0];

        $curl = curl_init();
        $api_key = $bankConfig['api_key'];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://oauth.casso.vn/v2/transactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: apikey $api_key",
                "Content-Type: application/json"
            ),
        )
        );
        $amount = $request->get('amount');
        $random = $request->get('random_code');

        $descriptionTemplate = "$random$amount";

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        ['data' => $data] = json_decode($response, true);

        $records = $data['records'];


        foreach ($records as $record) {
            $description = $record['description'];

            if (str_contains($description, $descriptionTemplate) && $record['amount'] == $amount) {
                $data = [
                    'isSuccess' => true,
                    'message' => 'Thanh toán thành công',
                ];

                return response($data, 200);
            }
        }

        $data = [
            'isSuccess' => false,
            'message' => 'Thanh toán thất bại',
        ];

        return response()->json($data);
    }

    public function store(CheckoutInvoiceRequest $request)
    {
        $cart = $request->input('cart');
        $voucherCode = $request->input('voucher_code');
        $customerPhoneNumber = $request->input('customer_phone_number');
        $tableNumber = $request->input('table_number');
        $staff = Auth::user();

        if ($customerPhoneNumber) {
            $customer = Customer::where('phone_number', $customerPhoneNumber)->first();

            if (!$customer) {
                $customer = CustomerService::createCustomer($customerPhoneNumber);
            }
        }

        if ($voucherCode) {
            [
                'isAvailable' => $isVoucherAvailable,
                'voucherType' => $voucherType,
                'voucherAmount' => $voucherAmount,
                'voucher' => $voucher,
                'quantity' => $quantity,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ] = VoucherService::verifyVoucher($voucherCode);

            $today = date('Y-m-d H:i:s');

            if (!$isVoucherAvailable || $quantity < 1 || $today > $endDate || $today < $startDate) {
                $data = [
                    'isSuccess' => false,
                    'message' => 'Voucher không hợp lệ hoặc đã hết hạn sử dụng, vui lòng xoá hoặc kiểm tra lại',
                ];

                return response($data, 200);
            }


            $customer = Customer::where('phone_number', $customerPhoneNumber)->first();

            $totalPrice = CartService::calculateCart($cart);
            [$discountPrice, $finalPrice] = CartService::applyVoucher($totalPrice, $voucherType, $voucherAmount);

            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$discountPriceLoyal, $finalPriceLoyal] = LoyalService::applyLoyal($finalPrice, $loyal);

                $invoice = Invoice::create([
                    'user_id' => $staff->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'table_number' => $tableNumber,
                    'voucher_code' => $voucherCode,
                    'note' => null,
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPriceLoyal,
                    'final_price' => $finalPriceLoyal,
                ]);
            } else {
                $invoice = Invoice::create([
                    'user_id' => $staff->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'table_number' => $tableNumber,
                    'voucher_code' => $voucherCode,
                    'note' => null,
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPrice,
                    'final_price' => $finalPrice,
                ]);
            }

            CartService::storeInvoiceDetail($cart, $invoice->id);

            $voucher->update([
                'quantity' => $quantity - 1,
            ]);
        } else {
            $totalPrice = CartService::calculateCart($cart);
            $customer = Customer::where('phone_number', $customerPhoneNumber)->first();

            if ($customerPhoneNumber) {
                $loyal = CustomerService::getCurrentLoyal($customerPhoneNumber);

                [$discountPriceLoyal, $finalPriceLoyal] = LoyalService::applyLoyal($totalPrice, $loyal);

                $invoice = Invoice::create([
                    'user_id' => $staff->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'table_number' => $tableNumber,
                    'voucher_code' => null,
                    'note' => null,
                    'total_price' => $totalPrice,
                    'discount_price' => $discountPriceLoyal,
                    'final_price' => $finalPriceLoyal,
                ]);
            } else {
                $invoice = Invoice::create([
                    'user_id' => $staff->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'table_number' => $tableNumber,
                    'voucher_code' => null,
                    'note' => null,
                    'total_price' => $totalPrice,
                    'discount_price' => 0,
                    'final_price' => $totalPrice,
                ]);
            }

            CartService::storeInvoiceDetail($cart, $invoice->id);
        }

        $data = [
            'isSuccess' => true,
            'message' => 'Đã checkout thành công',
        ];

        return response($data, 200);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->all());

        return response()->json($invoice);
    }

    public function show(Invoice $invoice)
    {
        $data = collect([$invoice, $invoice->invoiceDetails, $invoice->customer, $invoice->staff, $invoice->voucher]);

        return response()->json($data);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response('', 204);
    }

    public function destroyMultiple(MultipleDestroyRequest $request)
    {
        Invoice::destroy($request->ids);

        return response('Deleted successfully', 204);
    }

    public function getPending()
    {
        $pendingInvoices = Invoice::where('status', 'pending')->paginate();

        return response()->json($pendingInvoices);
    }
}