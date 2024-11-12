<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;

class RevenueService
{
    public function getRevenueByTimeRange($timeRange)
    {
        $currentYear = date('Y');

        if (preg_match('/^\d{2}$/', $timeRange)) {
            // Month only (MM) - tự động thêm năm hiện tại
            $timeRange = sprintf('%s/%s', $timeRange, $currentYear);
            return $this->getByMonth($timeRange);
        } elseif (preg_match('/^\d{2}\/\d{4}$/', $timeRange)) {
            // Specific month (MM/YYYY)
            return $this->getByMonth($timeRange);
        } elseif (preg_match('/^\d{2}\/\d{4}-\d{2}\/\d{4}$/', $timeRange)) {
            // Date range (MM/YYYY-MM/YYYY)
            list($startDate, $endDate) = explode('-', $timeRange);
            return $this->getByDateRange($startDate, $endDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $timeRange)) {
            // Specific date (DD/MM/YYYY)
            return $this->getByDate($timeRange);
        } else {
            // If unclear
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid time range format'
            ]);
        }
    }

    private function getByMonth($timeRange)
    {
        list($month, $year) = explode('/', $timeRange);
        return Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
    }

    private function getByDateRange($startDate, $endDate)
    {
        $startDate = Carbon::createFromFormat('d/m/Y', $startDate);
        $endDate = Carbon::createFromFormat('d/m/Y', $endDate);
        return Invoice::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
    }

    private function getByDate($date)
    {
        $date = Carbon::createFromFormat('d/m/Y', $date);
        return Invoice::whereDate('created_at', $date)->sum('amount');
    }
}
