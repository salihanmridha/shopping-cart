<?php

namespace App\Console\Commands;

use App\Jobs\DailySalesReportJob;
use Illuminate\Console\Command;

class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-sales {--date= : The date to generate report for (Y-m-d format, defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report to admin email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $this->info("Dispatching daily sales report for {$date->toDateString()}...");

        DailySalesReportJob::dispatch($date);

        $this->info('Daily sales report job dispatched successfully.');

        return Command::SUCCESS;
    }
}
