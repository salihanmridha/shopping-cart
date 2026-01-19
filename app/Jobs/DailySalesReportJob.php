<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DailySalesReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Carbon $date
    ) {}

    public function handle(SalesReportService $salesReportService): void
    {
        $salesData = $salesReportService->getDailySalesData($this->date);
        $adminEmail = config('shop.dummy_admin_email');

        Mail::to($adminEmail)->send(new DailySalesReport(
            date: $this->date,
            salesData: $salesData['items'],
            totalRevenue: $salesData['total_revenue'],
            totalQuantity: $salesData['total_quantity'],
            orderCount: $salesData['order_count']
        ));
    }
}
