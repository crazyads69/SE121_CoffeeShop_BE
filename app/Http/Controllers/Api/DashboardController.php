<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function getSummaryStatisticToday() {
        $startDay = time() - 86400 + (time() % 86400);
        $endDay = $startDay + 86400;

        $startYesterday = $startDay - 86400;
        $endYesterday = $startDay;

        $totalIncomeToday = 0;
        $totalIncomeYesterday = 0;
        $totalInvoiceToday = 0;
        $totalInvoiceYesterday = 0;
        $totalIncomePending = 0;
        $totalCustomerToday = 0;
        $totalCustomerYesterday = 0;

        $invoices = Invoice::all();

        foreach($invoices as $invoice) {
            if ($invoice->created_at->timestamp >= $startDay && $invoice->created_at->timestamp <= $endDay) {
                $totalIncomeToday += $invoice->total_price;
                $totalInvoiceToday++;
                if ($invoice->status == 'pending') {
                    $totalIncomePending += $invoice->total;
                }

                $invoicesDetails = $invoice->invoiceDetails()->get();

                foreach ($invoicesDetails as $invoiceDetail) {
                    $totalCustomerToday += $invoiceDetail->quantity;
                }
            }

            if ($invoice->created_at->timestamp >= $startYesterday && $invoice->created_at->timestamp <= $endYesterday) {
                $totalIncomeYesterday += $invoice->total;
                $totalInvoiceYesterday++;

                $totalCustomerYesterday += $invoice->invoiceDetails()->count();

                foreach ($invoice->invoiceDetails()->get() as $invoiceDetail) {
                    $totalCustomerYesterday += $invoiceDetail->quantity;
                }
            }
        }


        return response()->json([
            'total_income_today' => $totalIncomeToday,
            'total_income_yesterday' => $totalIncomeYesterday,
            'total_invoice_today' => $totalInvoiceToday,
            'total_invoice_yesterday' => $totalInvoiceYesterday,
            'total_income_pending' => $totalIncomePending,
            'total_customer_today' => $totalCustomerToday,
            'total_customer_yesterday' => $totalCustomerYesterday,
        ]);
    }

    private function getGranularity($distanceDate) {
        if ($distanceDate <= 29) {
            return 'day';
        }

        if ($distanceDate <= 87) {
            return 'week';
        }

        if ($distanceDate <= 367) {
            return 'month';
        }

        if ($distanceDate <= 1095) {
            return 'quarter';
        }

        return 'year';
    }

    private function getTimeWindows($startDate, $endDate, $granularity) {
        $times = [];

        $times[] = $startDate;

        while ($endDate - $startDate > 0) {
            if ($granularity == 'day') {
                $startDate += 86400;
            }

            if ($granularity == 'week') {
                $startDate += 86400*7;
            }

            if ($granularity == 'month') {
                $startDate += 86400*30;
            }

            if ($granularity == 'quarter') {
                $startDate += 86400*90;
            }

            if ($granularity == 'year') {
                $startDate += 86400*365;
            }

            $times[] = $startDate;
        }

        $lastElement = $times[count($times) - 1];
        $nearLastElement = $times[count($times) - 2];

        if ($lastElement - $nearLastElement <= 86400) {
            array_pop($times);
            array_pop($times);

            $times[] = $lastElement;
        }

        return $times;
    }

    private function getTimes($startDate, $endDate) {
        $distanceDate = (int)($endDate - $startDate)/86400;

        if ($distanceDate <= 7) {
            $startDate = $endDate - 86000*7;
        }

        $granularity = $this->getGranularity($distanceDate);

        $times = $this->getTimeWindows((int)$startDate, (int)$endDate, $granularity);

        return $times;
    }

    public function getIncomeByTime(DashboardRequest $request) {
        $startDate = (int)$request->get('start_date');
        $endDate = (int)$request->get('end_date');

        $times = $this->getTimes($startDate, $endDate);

        $invoices = Invoice::all();

        $incomesByTime = [];

        for($i = 0; $i < count($times) - 1; $i++) {
            $start = $times[$i];
            $end = $times[$i + 1];

            $incomesByTime[$times[$i]] = 0;

            foreach ($invoices as $invoice) {
                if ($invoice->created_at->timestamp >= $start && $invoice->created_at->timestamp <= $end) {
                    $incomesByTime[$times[$i]] += $invoice->total_price;
                }
            }
        }

        return response()->json($incomesByTime);
    }

    public function getTopProductByTime(DashboardRequest $request) {
        $startDate = (int)$request->get('start_date');
        $endDate = (int)$request->get('end_date');

        $topProducts = [];

        $invoicesAll = Invoice::all();
        $invoices = [];

        foreach ($invoicesAll as $invoice) {
            if ($invoice->created_at->timestamp >= $startDate && $invoice->created_at->timestamp <= $endDate) {
                $invoices[] = $invoice;
            }
        }

        foreach ($invoices as $invoice) {
            $invoiceDetails = $invoice->invoiceDetails;

            foreach ($invoiceDetails as $invoiceDetail) {
                $productName = $invoiceDetail->product_name;

                if (!isset($topProducts[$productName])) {
                    $topProducts[$productName] = $invoiceDetail->quantity;
                } else {
                    $topProducts[$productName] += $invoiceDetail->quantity;
                }
            }
        }

        return response()->json($topProducts);
    }

    public function getTotalCustomerByTime(DashboardRequest $request) {
        $startDate = (int)$request->get('start_date');
        $endDate = (int)$request->get('end_date');

        $times = $this->getTimes($startDate, $endDate);
        $invoices = Invoice::all();

        $customersByTime = [];

        for($i = 0; $i < count($times) - 1; $i++) {
            $start = $times[$i];
            $end = $times[$i + 1];

            $customersByTime[$times[$i]] = 0;

            foreach ($invoices as $invoice) {
                if ($invoice->created_at->timestamp >= $start && $invoice->created_at->timestamp <= $end) {
                    $invoiceDetails = $invoice->invoiceDetails;

                    foreach ($invoiceDetails as $invoiceDetail) {
                        $customersByTime[$times[$i]] += $invoiceDetail->quantity;
                    }
                }
            }
        }

        return response()->json($customersByTime);
    }
}
